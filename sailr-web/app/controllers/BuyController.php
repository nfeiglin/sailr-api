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

        return View::make('buy.create')
        ->with('title', 'Buying: ' .  $item->title)
        ->with('item', $item->toArray());
        ;
    }

    /**
     * Store a newly created resource in storage.
     * POST /buy/{id}
     * @param  int $id
     * @return Response
     */
    public function store($id)
    {
        return Redirect::back()->with('fail', 'test message mz');
        $input = Input::all();
        $item = Item::findOrFail($id)->with([
            'Shipping' => function ($x) {
                },
            'User' => function ($y) {
                }
        ])->get();

        $validator = Validator::make($input, BuyController::$rules);
        if ($validator->fails()) {
            return Redirect::back()->with('message', 'Invalid input')->withInput($input)->withErrors($validator->errors());

        }

        $shippingFee = 0.00;
        if ($item->country == $input['country']) {
            //Charge domestic shipping
            $shippingFee = $item->shipping->type->Domestic->price;
        } else {
            //Charge international shipping fee
            $shippingFee = $item->shipping->type->International->price;
        }

        //Calculate the amount the needs to be paid
        $total = $item->price + $shippingFee;

        //Make the Paypal payment
        $payRequest = new PayRequest();
        $receiver = array();

        $receiver[0] = new Receiver();
        $receiver[0]->amount = $total;
        $receiver[0]->email = $item->user->email;

        $receiverList = new ReceiverList($receiver);
        $payRequest->receiverList = $receiverList;

        //TODO: Finish these up
        $requestEnvelope = new RequestEnvelope("en_US");
        $payRequest->requestEnvelope = $requestEnvelope;
        $payRequest->actionType = "PAY";
        $payRequest->cancelUrl = "https://devtools-paypal.com/guide/ap_simple_payment/php?cancel=true";
        $payRequest->returnUrl = "https://devtools-paypal.com/guide/ap_simple_payment/php?success=true";
        $payRequest->currencyCode = $item->currency;
        $payRequest->ipnNotificationUrl = "http://replaceIpnUrl.com";

        //TODO: Change this to production when ready!
        $sdkConfig = Config::get('paypal.sandbox');

        $adaptivePaymentsService = new AdaptivePaymentsService($sdkConfig);
        $payResponse = $adaptivePaymentsService->Pay($payRequest);
        /*
                A JSON response will be returned containing paymentExecStatus and payKey — Save this!:

         {"responseEnvelope":{"timestamp":"2014-04-20T09:44:20.161-07:00",
        "ack":"Success","correlationId":"4f43a7d94dae4","build":"10273932"},
        "payKey":"AP-4EC942335R9066153","paymentExecStatus":"CREATED"}

        Take the payKey value from the response, add it to the following URL, and redirect your user there. PayPal will send the user back to the redirect URL provided in previous step.

            https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=AP-4EC942335R9066153
        */

        $dbPayResponse = new Feiglin\Payresponse();
        $dbPayResponse->paymentExecStatus = $payResponse->paymentExecStatus;
        $dbPayResponse->payKey = $payResponse->payKey;
        $dbPayResponse->user_id = Auth::user()->id;
        $dbPayResponse->item_id = $item->id;
        $dbPayResponse->save();

        //Now, redirect user to Paypal, where they can complete the payment.
        return Redirect::to('https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=' . $payResponse->payKey);
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