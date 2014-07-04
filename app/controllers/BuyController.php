<?php

use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\SellerDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use \PayPal\EBLBaseComponents\PaymentDetailsType;
use \PayPal\EBLBaseComponents\PaymentInfoType;
use \PayPal\PayPalAPI\SetExpressCheckoutReq;
use \PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use \PayPal\PayPalAPI\SetExpressCheckoutResponseType;
use \PayPal\PayPalAPI\GetExpressCheckoutDetailsResponseType;
use \PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use \PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use \PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use \PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use \PayPal\EBLBaseComponents\DoExpressCheckoutPaymentResponseDetailsType;
use PayPal\Core\PPAPIService;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;



class BuyController extends \BaseController
{

    public static $rules = [
        'country' => 'required|countryCode',
        'street_number' => 'required',
        'street_name' => 'required',
        'city' => 'required',
        'state' => 'required',
        'zipcode' => 'required'
    ];

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
            return Redirect::back()->withMessage('Sorry, the product has been made private by the seller. Please try again later');
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
            //->with('profileURL', $profileImg[0]['url']); //The current user's profile picture

    }

    /**
     * Store a newly created resource in storage.
     * POST /buy/{id}
     * @param  int $id
     * @return Response
     */
    public function store($id)
    {
        /*
         * Paypal express checkout smaple code
         * https://github.com/paypal/codesamples-php/blob/master/Merchant/sample/code/SetExpressCheckout.php
         * https://github.com/paypal/codesamples-php/blob/master/Merchant/sample/code/DoExpressCheckout.php
         */

        $merchant = new \Sailr\Emporium\Merchant\Merchant;
        $merchant->apiMode('sandbox')->product($id)->withBuyer(Auth::user())->setupPurchase();

        $input = Input::all();
        $buyerObject = Auth::user();

        if ($merchant->isProductPublic($item)) {
            return Redirect::back()->withMessage('Sorry, the product has been made private by the seller. Please try again later');
        }



        if ($item->user_id == $buyerObject->id) {
            return Redirect::back()->withMessage("You can't buy your own item");
        }

        if ($item->initial_units < 1) {
            return Redirect::back()->withMessage('Product sold out');
        }

        $validator = Validator::make($input, BuyController::$rules);
        if ($validator->fails()) {
            return Redirect::back()->with('message', 'Invalid input')->withInput($input)->withErrors($validator);

        }

        if ($input['country'] != $item->ships_to) {
            $messageBag = new \Illuminate\Support\MessageBag();
            $messageBag->add('country', 'The seller currently does not ship this item to ' . CountryHelpers::getCountryNameFromISOCode($input['country']));
            return Redirect::back()->with('message', 'Sorry...')->withErrors($messageBag);
        }

        $merchant->setupPurchase($item, $buyerObject, $input);

    }

    public function showConfirm($id)
    {
        //http://sailr.web/buy/53/confirm?token=EC-3CY03370AB277445T&PayerID=ZF5LCR3K9VJCU
        $config = Config::get('paypal.sandbox');
        $input = Input::all();
        $paypalToken = $input['token'];

        //Validate that the user is getting their own transaction not someone else's!
        $checkout = Checkout::where('id', '=', $id)->where('completed', '=', 0)->firstOrFail();
        $checkout->payerID = $input['PayerID'];
        $checkout->save();

        $item = Item::where('id', '=', $checkout->item_id)->with([
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

        if ($checkout->token != $paypalToken | $checkout->user_id != Auth::user()->id) {
            return Redirect::to('/')->with('fail', 'Sorry, you can only get Paypal transaction details for your own account. This transaction has not been processed and no money has been charged');
        }

        //$boughtUnits = Checkout::where('item_id', '=', $item->id)->where('completed', '=', 1)->count();

        if ($item->initial_units < 1) {
            return Redirect::to('/')->with('fail', 'Sorry. This item is now out of stock. You have not been charged and the transaction has not been processed');
        }



        if (!$item->public) {
            return Redirect::back()->withMessage('Sorry, the product has been made private by the seller. You have not been charged and the transaction has not been processed');
        }


        if ($item->user_id == Auth::user()->id) {
            return Redirect::back()->withMessage("You can't buy your own item. You have not been charged and the transaction has not been processed'");
        }



        $paypalService = new PayPalAPIInterfaceServiceService($config);
        $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($paypalToken);
        $getExpressCheckoutDetailsRequest->Version = '104.0';

        $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
        $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

        $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);

        if ($getECResponse->Errors) {

            if ($getECResponse->Errors[0]->ErrorCode == '10411') {
                //Paypal token expired
                return Redirect::to('/')->with('fail', 'Sorry, this Paypal session has expired please try starting the purchase again. This transaction has not been processed and you have not been charged');
            }
            return Redirect::to('/')->with('fail', 'Sorry, Paypal has encountered an error. This transaction has not been processed and you have not been charged');
        }

        //$item['photos'] = array(['url' => 'http://sailr.web/img/default-sm.jpg']);

        //return '<pre>' . print_r(json_decode(json_encode($getECResponse), true)) . '</pre>';

        $address = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->Address;
        $payment = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails;
        // dd($address);

        //return '<pre>' . print_r($x, 1) . '</pre>';
        return View::make('buy.confirm')
            ->with('title', 'Confirm purchase')
            ->with('item', $item)
            ->with('paypal', $getECResponse)
            ->with('address', $address)
            ->with('payment', $payment)
            ->with('id', $id)
            ->with('pp_token', $paypalToken);
    }

    public function doConfirm($id)
    {
        $config = Config::get('paypal.sandbox');
        $checkout = Checkout::findOrFail($id);

        //dd($checkout);
        $input = Input::all();
        $paypalToken = $checkout->token;

        $ipnUrl = URL::action('ipn');

        if ($checkout->user_id != Auth::user()->id) {
            return Redirect::to('/')->with('fail', 'Sorry, you can only get PayPal transaction details for your own account. This transaction has not been processed and no money has been charged');
        }


        $paypalService = new PayPalAPIInterfaceServiceService($config);
        $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($paypalToken);
        $getExpressCheckoutDetailsRequest->Version = '104.0';

        $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
        $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

        $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);


        ///dd($checkout->token);

        //echo '<pre>' . print_r($getECResponse) . '</pre>';
        //dd();

        if ($getECResponse->Errors) {

            if ($getECResponse->Errors[0]->ErrorCode == '10411') {
                //Paypal token expired

                Log::error('<pre>' . print_r($getECResponse) . '</pre>');
                return Redirect::to('/')->with('fail', 'Sorry, this Paypal session has expired please try starting the purchase again. This transaction has not been processed and you have not been charged');
            }

            Log::error('<pre>' . print_r($getECResponse) . '</pre>');
            return Redirect::to('/')->with('fail', 'Sorry, Paypal has encountered an error. This transaction has not been processed and you have not been charged');
        }



        $paymentDetails = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails;

        //$paymentDetails->PaymentAction = 'Sale';
        //$paymentDetails->NotifyURL = $ipnUrl;

        $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
        $DoECRequestDetails->PayerID = $checkout->payerID;
        $DoECRequestDetails->Token = $checkout->token;

        $DoECRequestDetails->PaymentDetails = $paymentDetails;
        $DoECRequest = new DoExpressCheckoutPaymentRequestType();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
        $DoECRequest->Version = '104.0';
        $DoECReq = new DoExpressCheckoutPaymentReq();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
        
        /* Right before the trasaction is commited and the user is charged we MUST verify there is sufficient stock of the item  */
        
        $item = Item::where('id', '=', $checkout->item_id)->firstOrFail(['id', 'initial_units']);
        //$boughtUnits = Checkout::where('item_id', '=', $item->id)->where('completed', '=', 1)->count();

        if ($item->initial_units < 1) {
            return Redirect::to('/')->with('fail', 'Sorry. This item is now out of stock. You have not been charged and the transaction has not been processed');
        }


        if (!$item->public) {
            return Redirect::to('/')->withMessage('Sorry, the product has been made private by the seller. You have not been charged and the transaction has not been processed');
        }


        if ($item->user_id == Auth::user()->id) {
            return Redirect::to('/')->withMessage("You can't buy your own item. You have not been charged and the transaction has not been processed'");
        }


        $DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
        //echo '<pre>' . print_r($DoECResponse, 1) . '</pre>';

        $paymentInfo = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0];

        if(isset($paymentInfo)) {
            $checkout->txn_id = $paymentInfo->TransactionID;
            $checkout->completed = 1; //Even if the payment isn't actually taken, we can't reuse the tokens etc so we need to start again anyway.
            $checkout->save();
        }
        if ($DoECResponse->Ack != "Success") {

            return Redirect::to('/')->with('message', 'We are afraid that the transaction has failed. Please try again.');
        }

        //echo '<pre>' . print_r($DoECResponse, 1) . '</pre>';


        $buyerID = Auth::user()->id;
        $sellerID = $item->user_id;
        $eventArray = [
            'buyer_user_id' => $buyerID,
            'seller_id' => $sellerID,
            'item_id' => $item->id,
            'payment_info' => $paymentInfo];

        switch($paymentInfo->PaymentStatus) {
            case "Completed":
                //Good news! it worked...
                Event::fire('purchase.completed', $eventArray);
                $item->initial_units = intval($item->initial_units) - 1;
                $item->save();
                return Redirect::to('/')->with('success', 'Purchase successful! Check your emails and notifications shortly for a confirmation');

                break;
            case "Created":
                $item->initial_units = intval($item->initial_units) - 1;
                $item->save();
                //A German ELV payment is made using Express Checkout.
                break;
            case "Denied":
                //The payment was denied. This happens only if the payment was previously pending because of one of the reasons listed for the pending_reason variable or the Fraud_Management_Filters_x var
                return Redirect::to('/')->with('message', 'The PayPal transaction was denied. Check your PayPal account for more info');
                break;
            case "Expired":
                //They took too long!
                return Redirect::to('/')->with('message', 'The PayPal transaction session has expired. No payment has been made. Please try again.');
                break;
            case "Failed":
                //Bank acct issues
                return Redirect::to('/')->with('message', 'The PayPal transaction failed. Check your PayPal account for more info');
                break;
            case "Pending":
                //Check the pending reason
                if ($paymentInfo->PendingReason == 'unilateral') {
                    //Tell the buyer to cancel the payment and seller to update their email.

                    //Event::fire('purchase.payment.pending.unilateral', $eventArray);
                    //echo '<pre>' . print_r($eventArray, 1) . '</pre>';
                    return Redirect::to('/')->withMessage('There has been an issue with the payment. Check your emails and PayPal account for more information');
                }

                else {
                    $item->initial_units = intval($item->initial_units) - 1;
                    $item->save();
                }
                //Go through the other pending reasons...
                break;
        }

        return Redirect::to('/')->with('message', 'We are afraid that the transaction may have failed. Please check your PayPal.');

    }


    public function cancel($id)
    {
        $input = Input::all();
        $checkout = Checkout::where('token', '=', $input['token'])->where('user_id', '=', Auth::user()->id)->firstOrFail();

        $checkout->delete();
        return Redirect::to('/')->with('message', Lang::get('transaction.cancel'));
        //dd($input);
    }



}