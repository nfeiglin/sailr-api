<?php

namespace Sailr\Handle;
use \Sailr\Emporium\Merchant\Webhooks\PaypalWebhook;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;

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

        if (!Lang::has($langBuyerString)) {
            $langBuyerString = 'ipn.buyer.pending.other';
        }

        if (!\Lang::has($langSellerString)) {
            $langSellerString = 'ipn.seller.pending.other';
        }

        $this->messageSellerPending($eventObject, $langSellerString);
        $this->messageBuyerPending($eventObject, $langBuyerString);
    }

    protected function messageSellerPending($eventObject, $langRetrivalString) {
        $errorMessage = \Lang::get($langRetrivalString);

        \Queue::push(function($job) use ($eventObject, $errorMessage) {

            $eventArray = (array) $eventObject;

            $user = \User::findOrFail($eventArray['sellerID']);
            $buyer = \User::findOrFail($eventArray['buyerID']);
            $product = \Item::findOrFail($eventArray['itemID']);

            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user,
                    'buyer' => $buyer,
                    'product' => $product,
                    'ipn' => $ipn,
                    'isPartial' => 1,
                    'errorReason' => $errorMessage];

            $view = \View::make('emails.purchase.error.seller', $data)->render();

            $notificationData = [
                'short_text' => "Issue with selling " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => 'purchase.error.pending',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            \Notification::create($notificationData);

            $sendToEmail = $user->email;
            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.error.seller', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject("There's been an issue selling your product");

            });


            $job->delete();
        });
    }

    protected function messageBuyerPending($eventObject, $langRetrievalString) {
        $errorMessage = \Lang::get($langRetrievalString);
        $eventArray = (array) $eventObject;

        \Queue::push(function($job) use ($eventArray, $errorMessage) {

            $user = \User::findOrFail($eventArray['buyerID']);
            $seller = \User::findOrFail($eventArray['sellerID']);
            $product = \Item::findOrFail($eventArray['itemID']);


            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user,
                'seller' => $seller,
                'product' => $product,
                'ipn' => $ipn,
                'checkout' => $checkout,
                'errorReason' => $errorMessage,
                'isPartial' => 1
            ];


            $view = \View::make('emails.purchase.error.buyer', $data)->render();


            $notificationData = [
                'short_text' => "Issue buying " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => 'purchase.error.pending',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            //dd($notificationData);

            \Notification::create($notificationData);

            $sendToEmail = $user->email;

            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.error.buyer', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject("There's been an issue with your purchase");

            });

            $job->delete();
        });

    }

    public function handleIpnError($eventObject) {

        $errorShortReason = $eventObject->errorShortReason;

        $langRetrivealString = "ipn.seller.error.$errorShortReason";

        if (!\Lang::has($langRetrivealString)) {
            $langRetrivealString = 'ipn.seller.error.default';
        }

        $this->messageSellerError($eventObject, $langRetrivealString);

        $langRetrivealString = "ipn.buyer.error.$errorShortReason";

        if (!\Lang::has($langRetrivealString)) {
            $langRetrivealString = 'ipn.buyer.error.default';
        }

        $this->messageBuyerError($eventObject, $langRetrivealString);




    }

    protected function messageSellerError($eventObject, $langRetrievalString) {
        $errorMessage = \Lang::get($langRetrievalString);
        $eventArray = (array)$eventObject;

        \Queue::push(function($job) use ($eventArray, $errorMessage) {

            $errorShortReason = $eventArray['errorShortReason'];

            $user = \User::findOrFail($eventArray['sellerID']);
            $buyer = \User::findOrFail($eventArray['buyerID']);
            $product = \Item::findOrFail($eventArray['itemID']);

            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user,
                'buyer' => $buyer,
                'product' => $product,
                'ipn' => $ipn,
                'isPartial' => 1,
                'errorReason' => $errorMessage];

            $view = \View::make('emails.purchase.error.seller', $data)->render();

            $notificationData = [
                'short_text' => "Issue with selling " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => "purchase.error.$errorShortReason",
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            \Notification::create($notificationData);

            $sendToEmail = $user->email;
            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.error.seller', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject("There's been an issue selling your product");

            });


            $job->delete();
        });

    }

    protected function messageBuyerError($eventObject, $langRetrievalString) {
        $errorMessage = \Lang::get($langRetrievalString);
        $eventArray = (array) $eventObject;

        \Queue::push(function($job) use ($eventArray, $errorMessage) {

            $user = \User::findOrFail($eventArray['buyerID']);
            $seller = \User::findOrFail($eventArray['sellerID']);
            $product = \Item::findOrFail($eventArray['itemID']);


            $ipnModel = \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder::findOrFail($eventArray['ipnID']);

            $ipn = PaypalWebhook::make($ipnModel);

            $checkout = \Checkout::where('txn_id', '=', $ipn->getTransactionIdentifier())->firstOrFail();

            $data = ['user' => $user,
                'seller' => $seller,
                'product' => $product,
                'ipn' => $ipn,
                'checkout' => $checkout,
                'errorReason' => $errorMessage,
                'isPartial' => 1
            ];


            $view = \View::make('emails.purchase.error.buyer', $data)->render();


            $notificationData = [
                'short_text' => "Issue buying " . \Str::limit($product->title, 40),
                'long_html' => $view,
                'type' => 'purchase.error.pending',
                'user_id' => $user->id,
                'data' => ['product' => $product->toArray(), 'checkout' => $checkout->toArray()],
            ];

            //dd($notificationData);

            \Notification::create($notificationData);

            $sendToEmail = $user->email;

            $data['isPartial'] = 0;

            \Mail::send('emails.purchase.error.buyer', $data, function($message) use ($sendToEmail) {
                $message->to($sendToEmail);
                $message->subject("There's been an issue with your purchase");

            });

            $job->delete();
        });

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