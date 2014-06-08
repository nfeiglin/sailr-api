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
use \PayPal\PayPalAPI\DoExpressCheckoutPaymentResponseType;
//use Paypal\EBLBaseComponents\PaymentDetailsType;


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
     * GET /buy/{id}/create
     * @param  int $id
     * @return Response
     */
    public function create($id)
    {

        $item = Item::where('id', '=', $id)->with([
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
                    $y->where('type', '=', 'full_res');
                    $y->select(['item_id', 'type', 'url']);

                },
        ])->firstOrFail();

        //$domesticShippingPrice = $item->shipping->type->Domestic->price;
        //$internationalShippingPrice = $item->shipping->type->International->price;
        $profileImg = false;
        if (Auth::check()) {
            $profileImg = ProfileImg::where('user_id', '=', Auth::user()->id)->where('type', '=', 'small')->get(['url'])->toArray();

        }
        $profileImg = [0 => array('url' => 'http://sailr.web/img/default-sm.jpg')];

        return View::make('buy.create')
            ->with('title', 'Buying: ' . $item->title)
            ->with('item', $item->toArray())
            ->with('profileURL', $profileImg[0]['url']);

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
         *
         *
         */

        /*
              '_token' => string '2fNauE5l2OGRV3Z45hPn5B5nKGGNNqgthfwmGP7z' (length=40)
  'street_number' => string '275' (length=3)
  'street_name' => string 'Kent St' (length=7)
  'city' => string 'Sydney' (length=6)
  'state' => string 'NSW' (length=3)
  'zipcode' => string '2000' (length=4)
  'country' => string 'Australia' (length=9)
         */
        $input = Input::all();
        $u = new User();
        $u->setHidden([]);

        $item = Item::where('id', '=', $id)->with([
            'User' => function ($y) {

                }
        ])->firstOrFail();

        $itemArray = $item->toArray();

        if ($item->user_id == Auth::user()->id) {
            return Redirect::back()->withMessage("You can't buy your own item");
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


        $shippingFee = $item->ship_price;

        //Store some initial info in the database
        $checkout = new Checkout();
        $checkout->item_id = $item->id;
        $checkout->user_id = Auth::user()->id;
        $checkout->completed = 0;
        $checkout->save();

        //$checkoutSaved = Checkout::find($checkout->id);

        //Calculate the amount the needs to be paid
        $total = floatval($item->price) + $shippingFee;

        $config = Config::get('paypal.sandbox');

        $baseURL = URL::to('/');
        $returnURL = $baseURL . '/buy/' . $checkout->id . '/confirm';
        $cancelURL = $baseURL . '/buy/' . $checkout->id . '/cancel';

        if ($config['mode'] == 'LIVE') {
            $returnURL = URL::action('BuyController@showConfirm', $checkout->id);
            $cancelURL = URL::action('Controller@cancel', $checkout->id);
        }


        $paymentAction = 'Sale';
        $address1 = $input['street_number'] . ' ' . $input['street_name'];

        $sellerEmail = $item->user->email;
        $invoice_id = substr(sha1($checkout->id . microtime()), 0, 32);

        $setExpressCheckoutRequestDetails = new SetExpressCheckoutRequestDetailsType();
        $setExpressCheckoutRequestDetails->ReturnURL = $returnURL;
        $setExpressCheckoutRequestDetails->CancelURL = $cancelURL;

        $setExpressCheckoutRequestDetails->MaxAmount = new BasicAmountType($item->currency, $total);


// ### Payment Information
        // list of information about the payment
        $paymentDetailsArray = array();

        // information about the first payment
        $paymentDetails1 = new PaymentDetailsType();

        // Total cost of the transaction to the buyer. If shipping cost and tax
        // charges are known, include them in this value. If not, this value
        // should be the current sub-total of the order.
        //
        // If the transaction includes one or more one-time purchases, this field must be equal to
        // the sum of the purchases. Set this field to 0 if the transaction does
        // not include a one-time purchase such as when you set up a billing
        // agreement for a recurring payment that is not immediately charged.
        // When the field is set to 0, purchase-specific fields are ignored.
        //
        // * `Currency Code` - You must set the currencyID attribute to one of the
        // 3-character currency codes for any of the supported PayPal
        // currencies.
        // * `Amount`

        $orderTotal1 = new BasicAmountType($item->currency, $total);
        $paymentDetails1->OrderTotal = $orderTotal1;

        $itemAmount = new BasicAmountType($item->currency, floatval($item->price));
        $paymentDetails1->ShippingTotal = new BasicAmountType($item->currency, $shippingFee);
        $paymentDetails1->ItemTotal = $itemAmount;
        $paymentDetails1->OrderDescription = str_limit($item->title, 124);

        // How you want to obtain payment. When implementing parallel payments,
        // this field is required and must be set to `Order`. When implementing
        // digital goods, this field is required and must be set to `Sale`. If the
        // transaction does not include a one-time purchase, this field is
        // ignored. It is one of the following values:
        //
        // * `Sale` - This is a final sale for which you are requesting payment
        // (default).
        // * `Authorization` - This payment is a basic authorization subject to
        // settlement with PayPal Authorization and Capture.
        // * `Order` - This payment is an order authorization subject to
        // settlement with PayPal Authorization and Capture.
        // `Note:
        // You cannot set this field to Sale in SetExpressCheckout request and
        // then change the value to Authorization or Order in the
        // DoExpressCheckoutPayment request. If you set the field to
        // Authorization or Order in SetExpressCheckout, you may set the field
        // to Sale.`
        $paymentDetails1->PaymentAction = $paymentAction;

        // Unique identifier for the merchant. For parallel payments, this field
        // is required and must contain the Payer Id or the email address of the
        // merchant.
        $sellerDetails1 = new SellerDetailsType();
        $sellerDetails1->PayPalAccountID = $sellerEmail;
        $sellerDetails1->SellerUserName = $item->user->username;
        $sellerDetails1->SellerId = $item->user->id;

        $paymentDetails1->SellerDetails = $sellerDetails1;
        $paymentDetails1->InvoiceID = $invoice_id;

        $paypalItem = new PaymentDetailsItemType();

        $paypalItem->Amount = $itemAmount;
        $paypalItem->Description = str_limit($item->description);
        $paypalItem->Name = str_limit($item->title, 124);
        $paypalItem->Quantity = 1;
        $paypalItem->Number = $item->id;
        $paypalItem->ItemURL = action('BuyController@create', $item->id);
        $paypalItem->ItemCategory = 'Physical';

        $paymentDetails1->PaymentDetailsItem = $paypalItem;

        // A unique identifier of the specific payment request, which is
        // required for parallel payments.
        $paymentDetails1->PaymentRequestID = sha1(microtime());

        // `Address` to which the order is shipped, which takes mandatory params:
        //
        // * `Street Name`
        // * `City`
        // * `State`
        // * `Country`
        // * `Postal Code`
        $shipToAddress1 = new AddressType();
        $shipToAddress1->Street1 = $address1;
        $shipToAddress1->CityName = $input['city'];
        $shipToAddress1->StateOrProvince = $input['state'];
        $shipToAddress1->Country = $input['country'];
        $shipToAddress1->PostalCode = $input['zipcode'];

        // Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
        $paymentDetails1->NotifyURL = "http://localhost/ipn";

        $paymentDetails1->ShipToAddress = $shipToAddress1;

        $setExpressCheckoutRequestDetails->AllowNote = 1;
        $setExpressCheckoutRequestDetails->BrandName = 'Sailr';

        //$setExpressCheckoutRequestDetails->NoShipping = 1;
        $setExpressCheckoutRequestDetails->AddressOverride = 1;
        $paymentDetailsArray[0] = $paymentDetails1;

        $setExpressCheckoutRequestDetails->PaymentDetails = $paymentDetailsArray;

        $setExpressCheckoutReq = new SetExpressCheckoutReq();

        $setExpressCheckoutRequest = new SetExpressCheckoutRequestType($setExpressCheckoutRequestDetails);

        $setExpressCheckoutReq->SetExpressCheckoutRequest = $setExpressCheckoutRequest;

        // ## Creating service wrapper object
        // Creating service wrapper object to make API call and loading
        // configuration file for your credentials and endpoint

        $service = new PayPalAPIInterfaceServiceService($config);


        $response = $service->SetExpressCheckout($setExpressCheckoutReq);

        //try {
        // ## Making API call
        // Invoke the appropriate method corresponding to API in service
        // wrapper object



        //} catch (Exception $ex) {
        //  Log::error("Error Message : " . $ex->getMessage());

        //  }


        // ## Accessing response parameters
        // You can access the response parameters using variables in
        // response object as shown below
        // ### Success values
        if ($response->Ack == "Success") {

            // ### Redirecting to PayPal for authorization
            // Once you get the "Success" response, needs to authorise the
            // transaction by making buyer to login into PayPal. For that,
            // need to construct redirect url using EC token from response.
            // For example,
            // `redirectURL="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=". $response->Token();`

            // Express Checkout Token
            //Log::debug("EC Token:" . $response->Token);

            $checkout->ack = $response->Ack;
            $checkout->token = $response->Token;
            $checkout->save();

            if ($config['mode'] == 'LIVE') {
                return Redirect::to('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->Token);
            } else {
                return Redirect::to('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->Token);
            }


        } // ### Error Values
        // Access error values from error list using getter methods
        else {
            Log::error("API Error Message : " . $response->Errors[0]->LongMessage);
            dd($response);
        }


        /*
        $sdkConfig = Config::get('paypal.sandbox');

        $dbPayResponse = new Feiglin\Payresponse();
        $dbPayResponse->paymentExecStatus = $payResponse->paymentExecStatus;
        $dbPayResponse->payKey = $payResponse->payKey;
        $dbPayResponse->user_id = Auth::user()->id;
        $dbPayResponse->item_id = $item->id;
        $dbPayResponse->save();
        */

        //Now, redirect user to Paypal, where they can complete the payment.
        //return Redirect::to('https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=' . $payResponse->payKey);
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

        $boughtUnits = Checkout::where('item_id', '=', $item->id)->where('completed', '=', 1)->count();

        if ($boughtUnits >= $item->initial_units) {
            return Redirect::to('/')->with('fail', 'Sorry. This item is now out of stock. You have not been charged and the transaction has not been processed');
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

        $item['photos'] = array(['url' => 'http://sailr.web/img/default-sm.jpg']);

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

        $ipnUrl = 'http://localhost.com';

        if ($checkout->user_id != Auth::user()->id) {
            return Redirect::to('/')->with('fail', 'Sorry, you can only get Paypal transaction details for your own account. This transaction has not been processed and no money has been charged');
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
        $boughtUnits = Checkout::where('item_id', '=', $item->id)->where('completed', '=', 1)->count();

        if ($boughtUnits >= $item->initial_units) {
            return Redirect::to('/')->with('fail', 'Sorry. This item is now out of stock. You have not been charged and the transaction has not been processed');
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
                return Redirect::to('/')->with('success', 'Purchase successful! Check your emails and notifications shortly for a confirmation');

                break;
            case "Created":
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

                    Event::fire('purchase.payment.pending.unilateral', $eventArray);
                    //echo '<pre>' . print_r($eventArray, 1) . '</pre>';
                    return Redirect::to('/')->withMessage('There has been an issue with the payment. Check your emails and PayPal account for more information');
                }
                //Go through the other pending reasons...
                break;
            /*
            case "Reversed":
                //A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
                break;
            case "Processed":
                //Payment accepted
                break;
            case "Voided":
                //This authorization has been voided.
                break;
            */
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