<?php

use Sailr\Validators\PurchaseValidator;
use Sailr\Emporium\Merchant\Merchant;
use Sailr\Emporium\Merchant\Exceptions\PayPalApiErrorException;
use Sailr\Emporium\Merchant\Entity\PayPalAddressEntity;
use Sailr\Emporium\Merchant\Exceptions\PayPalSessionExpiredException;
use Sailr\Emporium\Merchant\Exceptions\PayPalResponseNotSuccessException;
use Sailr\Emporium\Merchant\Exceptions\TokenDoesNotMatchLoggedInAccountException;
use Sailr\Validators\Exceptions\ValidatorException;

class BuyController extends \BaseController
{
    /**
     * @var Sailr\Emporium\Merchant\Merchant $merchant
     * @var Sailr\Validators\PurchaseValidator $validator
     */
    protected $validator;
    protected $merchant;
    public function __construct(PurchaseValidator $validator, Merchant $merchant) {

        $this->validator = $validator;
        $this->merchant = $merchant;

        $apiMode = Str::lower(getenv('PAYPAL_API_MODE'));

        $this->merchant
            ->config(Config::get("paypal.$apiMode"))
            ->apiMode($apiMode)
            ->webhookUrl(URL::action('ipn'));
    }


    /**
     * Show the form for creating a new resource.
     * GET /{username}/product/{id}
     * @param string $username
     * @param  int $id
     * @return Response
     */
    public function create($username, $id)
    {

        $item = Item::where('id', '=', $id)->with([
            'User' => function ($y) {
                $y->select(['id', 'username', 'name']);
                },

            'Photos' => function ($y) {
                    $y->where('type', '=', 'full_res');
                    $y->select(['item_id', 'type', 'url']);

                },
        ])->firstOrFail();


        if (!$item->public) {

            return Redirect::to('/')->with('message','Sorry, the product has been made private by the seller. Please try again later');
        }

        $item->user->profile_img = ProfileImg::where('user_id', '=', $item->user->id)->where('type', '=', 'small')->first(['url']);

        if ($item->user->username != $username) {
            $exception = new \Illuminate\Database\Eloquent\ModelNotFoundException;
            $exception->setModel('ITEM Username does not match item');
            throw $exception;
        }


        return View::make('buy.create')
            ->with('title', $item->title)
            ->with('item', $item->toArray());

    }

    /**
     * Store a newly created resource in storage.
     * POST /buy/{id}
     * @param  int $id
     * @return Response
     */
    public function store($id)
    {

        $input = Input::all();
        $buyerObject = Auth::user();
        $item = Item::where('id', '=', $id)->with('User')->firstOrFail();

        if ($item->user_id == $buyerObject->id) {
            return Redirect::to('/')->withMessage("You can't buy your own product");
        }

        $validateInput = $input;
        $validateInput['product'] = $item;

        $result = $this->validator->validate($validateInput, 'create');

        if (!$result) {
            return Redirect::to('/')->with('message', 'Invalid input')->withInput($input)->withErrors($this->validator->getValidator());

        }

        if ($input['country'] != $item->ships_to) {
            $messageBag = new \Illuminate\Support\MessageBag();
            $messageBag->add('country', 'The seller currently does not ship this item to ' . CountryHelpers::getCountryNameFromISOCode($input['country']));
            return Redirect::to('/')->with('message', 'Sorry...')->withErrors($messageBag);
        }


        $addressEntity = PayPalAddressEntity::make(new stdClass());
        $addressEntity->setAddress1($input['street_number'] . ' ' . $input['street_name']);
        $addressEntity->setCity($input['city']);
        $addressEntity->setState($input['state']);
        $addressEntity->setCountryCode($input['country']);
        $addressEntity->setZipCode($input['zipcode']);

        try {
            $displayName = "@" . htmlentities($item->user->username) . " on Sailr";

           return Redirect::to(
               $this->merchant
                ->product($item)
                ->buyer($buyerObject)
                ->withSellerDisplayName($displayName)
                ->withAddress($addressEntity)
                ->setupPurchase()
               ->getRedirectUrl()
           );
        }
        catch (PayPalApiErrorException $e) {

            return Redirect::to('/')->with('message', 'PayPal has encountered an error');
        }


    }

    public function showConfirm($id)
    {
        //http://sailr.web/buy/53/confirm?token=EC-3CY03370AB277445T&PayerID=ZZZZZZZZZZ

        $checkout = Checkout::where('id', '=', $id)->where('completed', '=', 0)->firstOrFail();
        $item =  Item::where('id', '=', $checkout->item_id)->with([
            'User' => function ($y) {
                $y->with([
                    'ProfileImg' => function ($z) {
                        $z->where('type', '=', 'small');
                        $z->first();
                    }
                ]);
                $y->select(['id', 'username', 'name']);
            },

            'Photos' => function ($y) {
                $y->where('type', '=', 'thumbnail');
                $y->select(['item_id', 'type', 'url']);
            },
        ])->firstOrFail();

        $result = $this->validator->validate(['product' => $item], 'confirmPurchase');

        if (!$result) {
            return Redirect::to('/')->with('message', 'There has been an issue...')->withErrors($this->validator->getValidator());
        }

        try {
            $merchant = $this->merchant
                ->product($item)
                ->buyer(Auth::user())
                ->withCheckout($checkout)
                ->withPayerId(Input::get('PayerID'))
                ->withPaypalToken(Input::get('token'))
                ->getConfirmationDetails();
        }

        catch (TokenDoesNotMatchLoggedInAccountException $e) {
            return Redirect::to('/')->with('message', 'You can only confirm purchases for your own account. The transaction has been canceled and you have not been charged');
        }

        catch(PayPalSessionExpiredException $e) {
            return Redirect::to('/')->with('message', 'Oops. The PayPal session has expired. The transaction has been canceled and you have not been charged. Please try purchasing product again.');
        }

        catch(PayPalApiErrorException $e) {
            return Redirect::to('/')->with('message', 'PayPal has encountered an error. The transaction has been canceled and you have not been charged.');
        }



        return View::make('buy.confirm')
            ->with('title', 'Confirm purchase')
            ->with('item', $merchant->product()->toArray())
            ->with('address', $merchant->address())
            ->with('payment', $merchant->getPaymentDetails())
            ->with('id', $id)
            ->with('pp_token', $merchant->getPaypalToken());
    }

    public function doConfirm($id)
    {

        $checkout = Checkout::findOrFail($id);

        try {
            $redirectEntity = $this->merchant
                ->buyer(Auth::user())
                ->withCheckout($checkout)
                ->doPurchaseProduct()
                ->redirectEntity();

           return Redirect::to('/')->with($redirectEntity->type, $redirectEntity->message);

        }

        catch (ValidatorException $e) {
            return Redirect::to('/')->withErrors($e->getValidator());
        }

        catch (PayPalSessionExpiredException $e) {
            return Redirect::to('/')->with('fail', 'Sorry, this PayPal session has expired please try starting the purchase again. This transaction has not been processed and you have not been charged');
        }

        catch (PayPalApiErrorException $e) {
            return Redirect::to('/')->with('fail', 'Sorry, PayPal has encountered an error. This transaction has not been processed and you have not been charged');
        }

        catch (PayPalResponseNotSuccessException $e) {
            $redirectEntity = $this->merchant->redirectEntity();
            return Redirect::to('/')->with($redirectEntity->type, $redirectEntity->message);
        }


    }


    public function cancel($id)
    {
        $checkout = Checkout::where('token', '=', Input::get('token'))->where('user_id', '=', Auth::user()->id)->firstOrFail();
        $checkout->delete();

        return Redirect::to('/')->with('message', Lang::get('transaction.cancel'));
    }



}