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

            $eventArray = ['itemID' => $checkout->item_id, 'buyerID' => $checkout->user_id, 'sellerID' => $sellerID, 'ipn' => $order];

            switch($order->payment_status) {
                case "Completed":
                    //Good news! it worked...
                    Event::fire('ipn.success.completed', $eventArray);

                    break;
                case "Created":
                    //A German ELV payment is made using Express Checkout.
                    Event::fire('ipn.success.created', $eventArray);
                    break;
                case "Denied":
                    //The payment was denied. This happens only if the payment was previously pending because of one of the reasons listed for the pending_reason variable or the Fraud_Management_Filters_x var
                    Event::fire('ipn.fail.denied', $eventArray);
                    break;
                case "Expired":
                    //They took too long!

                    break;
                case "Failed":
                    //Bank acct issues
                    Event::fire('ipn.fail.failed', $eventArray);
                    break;
                case "Pending":
                    //Check the pending reason
                    if ($order->pending_reason == 'unilateral') {

                        Event::fire('ipn.fail.unilateral', $eventArray);
                    }
                    //Tell the buyer to cancel the payment and seller to update their email.

                    //Go through the other pending reasons...
                    break;

                case "Reversed":
                    //A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
                    Event::fire('ipn.success.reversed', $eventArray);
                    break;
                case "Processed":
                    //Payment accepted
                    Event::fire('ipn.success.processed', $eventArray);
                    break;
                case "Voided":
                    //This authorization has been voided.
                    Event::fire('ipn.fail.voided', $eventArray);
                    break;

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
