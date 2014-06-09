<?php

class IpnController extends \BaseController {

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        //http://cf5fa45.ngrok.com/payment/ipn
        $resCode = 500;
        Log::debug('IPN handler hit', Input::all());

        try {
            $order = IPN::getOrder();
            $jsonOrder = $order->toJSON();
            Log::debug('IPN Received:::: ' . $jsonOrder);
            $checkout = Checkout::where('txn_id', '=', $order->txn_id)->first();

            //$checkoutArray= $checkoutArray->toArray();
            //$checkoutArray = Checkout::where('txn_id', '=', $order->txn_id)->withTrashed()->get()->toArray();

            if (isset($checkout)) {
                $buyerID = $checkout->user_id;
                $item = Item::where('id', '=', $checkout->item_id)->first();

                $jsonItem = $item->toJson();
                Log::debug('ITEM FOR IPN::: '. $jsonItem);

                $sellerID = $item->user_id;

                Log::debug('Buyer ID for the IPN (txn_id: ' . $order->txn_id . ')<p> ' . $buyerID . '</p> Seller ID: ' . $sellerID);
                $resCode = 200; //All is good
            }

            else {
                Log::alert('No Checkout model found for transaction ID: ' . $order->txn_id);
            }


        }

        catch (LogicalGrape\PayPalIpnLaravel\Exception\InvalidIpnException $e) {
            Log::error('Invalid Paypal IPN sent');
            $resCode = 500;
        }

        //TODO: Fire some events and send out notifications if it worked, was delayed, or failed.

        return Response::make('', $resCode);
	}


}
