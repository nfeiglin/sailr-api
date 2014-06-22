<?php

namespace Sailr\Handle;
use \Sailr\Emporium\Merchant\Webhooks\PaypalWebhook;

class IpnEventHandler {

    // $eventArray = ['itemID' => $checkout->item_id, 'buyerID' => $checkout->user_id, 'sellerID' => $sellerID, 'ipn' => $order];

    public function onPaymentSuccess(array $eventArray) {


        $eventArray['ipnID'] = $eventArray['ipn']->id;

        $this->messageBuyerSuccess($eventArray);
        $this->messageSellerSuccess($eventArray);


   }

    protected function messageBuyerSuccess(array $eventArray) {

        \Queue::push(function($job) use ($eventArray) {

            $user = \User::findOrFail($eventArray['buyerID']);
            $seller = \User::findOrFail($eventArray['sellerID']);
            $product = \Item::findOrFail($eventArray['itemID']);


            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->txn_id)->firstOrFail();

            $data = ['user' => $user, 'seller' => $seller, 'product' => $product, 'ipn' => $ipn, 'checkout' => $checkout;

            \Notification::create([
                'short_text' => "Purchased " . \Str::limit($product->title, 40),
                'long_html' => \View::make('emails.purchase.receipt', $data)->with('isPartial', 1),
                'type' => 'purchase.create',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ]);

            $email = $user->email;
            \Mail::queue('emails.purchase.receipt', $data, function($message) use ($email) {
                $message->to($email);
                $message->subject('Congratulations on your purchase');

            });

            $job->delete();
        });

    }

    protected function messageSellerSuccess(array $eventArray) {

        \Queue::push(function($job) use ($eventArray) {

            $user = \User::findOrFail($eventArray['sellerID']);
            $buyer = \User::findOrFail($eventArray['buyerID']);
            $product = \Item::findOrFail($eventArray['itemID']);

            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->txn_id)->firstOrFail();

            $data = ['user' => $user, 'buyer' => $buyer, 'product' => $product, 'ipn' => $ipn, 'isPartial' => 1];

            \Notification::create([
                'short_text' => "Purchased " . \Str::limit($product->title, 40),
                'long_html' => \View::make('emails.purchase.seller', $data)->with('isPartial', 1),
                'type' => 'purchase.create',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ]);

            $sendToEmail = $user->email;
            \Mail::queue('emails.purchase.sold', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject('Your product has sold');

            });


            $job->delete();
        });

    }
} 