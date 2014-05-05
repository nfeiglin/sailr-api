<?php

class BuyController extends \BaseController
{

    public static $rules = ['country' => 'required|country'];

    /**
     * Show the form for creating a new resource.
     * GET /buy/{id}/create
     * @param  int $id
     * @return Response
     */
    public function create($id)
    {

        $item = Item::where('id', '=', $id)->with([
            'Shipping' => function ($x) {
                $x->select(['item_id', 'type', 'price', 'desc']);
                },
            'User' => function ($y) {
                $y->with([
                            'ProfileImg' => function($z) {
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
        if(Auth::check()) {
            $profileImg = ProfileImg::where('user_id', '=', Auth::user()->id)->where('type', '=', 'small')->get(['url'])->toArray();

        }
        $profileImg = [0 => array('url' => 'http://sailr.web/img/default-sm.jpg')];

        return View::make('buy.create')
        ->with('title', 'Buying: ' .  $item->title)
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
        'Shipping' => function ($x) {
            },
        'User' => function ($y) {

            }
    ])->firstOrFail();

        $itemArray = $item->toArray();


        $validator = Validator::make($input, BuyController::$rules);
        if ($validator->fails()) {
            return Redirect::back()->with('message', 'Invalid input')->withInput($input)->withErrors($validator);

        }

        $shippingFee = 0.00;
        $shippingName = 'shipping';
        if ($item->country == $input['country']) {
            //Charge domestic shipping
            $shippingFee = $item['shipping'][0]['price'];
            $shippingName = 'Domestic ' . $shippingName;
        } else {
            //Charge international shipping fee
            $shippingFee = $itemArray['shipping'][1]['price'];
            $shippingName = 'International ' . $shippingName;
        }

        //Store some initial info in the database
        $checkout = new Checkout();
        $checkout->item_id = $item->id;
        $checkout->user_id = Auth::user()->id;
        $checkout->completed = 0;
        $checkout->save();

        //$checkoutSaved = Checkout::find($checkout->id);

        //Calculate the amount the needs to be paid
        $total = floatval($item->price) + $shippingFee;

        $baseURL = 'http://sailr.web';
        $returnURL = $baseURL . '/buy/' . $checkout->id . '/confirm';
        $cancelURL = $baseURL . '/buy/' . $checkout->id . '/cancel';
        $paymentAction = 'Sale';
        $address1 = $input['street_number'] . ' ' . $input['street_name'];
        $config = Config::get('paypal.sandbox');
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

        $itemAmount =  new BasicAmountType($item->currency, floatval($item->price));
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
        $shipToAddress1->Country = CountryHelpers::getISOCodeFromCountryName($input['country']);
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
       // $response = new PayPalAPIInterfaceServiceService($config);
        //try {
            // ## Making API call
            // Invoke the appropriate method corresponding to API in service
            // wrapper object

            $response = $service->SetExpressCheckout($setExpressCheckoutReq);


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
            Log::debug("EC Token:" . $response->Token);

            $checkout->ack = $response->Ack;
            $checkout->token = $response->Token;
            $checkout->save();

            if ($config['mode'] == 'LIVE') {
                return Redirect::to('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='. $response->Token);
            }

            else {
                return Redirect::to('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='. $response->Token);
            }


        }
        // ### Error Values
        // Access error values from error list using getter methods
        else {
            Log::error("API Error Message : ". $response->Errors[0]->LongMessage);
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

    public function showConfirm($id) {
        //
    }

    public function payment($id) {
        $itemID = $id;
        $input = Input::all();
        dd($input);
    }

    public function cancel($id) {
        $input = Input::all();
        $checkout = Checkout::where('token', '=', $input['token'])->where('user_id', '=', Auth::user()->id)->firstOrFail();

        $checkout->delete();
        return Redirect::to('/')->with('message', Lang::get('transaction.cancel'));
        //dd($input);
    }
    /**
     * Display the specified resource.
     * GET /buy/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /buy/{id}/edit
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * PUT /buy/{id}
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }


}