<?php

namespace Sailr\Handle;
use \Sailr\Emporium\Merchant\Webhooks\PaypalWebhook;

class IpnEventHandler {

    // $eventArray = ['itemID' => $checkout->item_id, 'buyerID' => $checkout->user_id, 'sellerID' => $sellerID, 'ipn' => $order];

    public function onPaymentSuccess($eventArray) {

        $eventArray = (array)$eventArray;
        //dd($eventArray);

        $ipn = $eventArray['ipn'];
        \Log::debug('SENT TO onPaymentSuccess::: ' . print_r($eventArray, 1));
        $eventArray['ipnID'] = $ipn->id;

        $this->messageBuyerSuccess($eventArray);
        $this->messageSellerSuccess($eventArray);

   }

    public function onTransactionPending(\stdClass $eventObject) {

        $langBuyerString = "ipn.buyer.pending.$eventObject->pending_reason";
        $langSellerString = "ipn.seller.pending.$eventObject->pending_reason";

        $this->messageSellerPending($eventObject, $langSellerString);
        $this->messageBuyerPending($eventObject, $langBuyerString);
    }

    protected function messageSellerPending($eventObject, $langRetrivalString) {

    }

    protected function messageBuyerPending($eventObject, $langRetrivalString) {

    }

    public function messageBuyerSuccess(array $eventArray) {

        \Queue::push(function($job) use ($eventArray) {

            $user = \User::findOrFail($eventArray['buyerID']);
            $seller = \User::findOrFail($eventArray['sellerID']);
            $product = \Item::findOrFail($eventArray['itemID']);


            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user, 'seller' => $seller, 'product' => $product, 'ipn' => $ipn, 'checkout' => $checkout, 'isPartial' => 1];


            $view = \View::make('emails.purchase.receipt', $data)->render();


            $notificationData = [
                'short_text' => "Purchased " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => 'purchase.create',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            //dd($notificationData);

            \Notification::create($notificationData);

            $sendToEmail = $user->email;

            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.receipt', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject('Congratulations on your purchase');

            });

            $job->delete();
        });

    }

    public function messageSellerSuccess(array $eventArray) {

        \Queue::push(function($job) use ($eventArray) {

            $user = \User::findOrFail($eventArray['sellerID']);
            $buyer = \User::findOrFail($eventArray['buyerID']);
            $product = \Item::findOrFail($eventArray['itemID']);

            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user, 'buyer' => $buyer, 'product' => $product, 'ipn' => $ipn, 'isPartial' => 1];

            $view = \View::make('emails.purchase.seller', $data)->render();

            $notificationData = [
                'short_text' => "Sold " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => 'purchase.create',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            \Notification::create($notificationData);

            $sendToEmail = $user->email;
            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.seller', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject('Your product has sold');

            });


            $job->delete();
        });

    }
} 