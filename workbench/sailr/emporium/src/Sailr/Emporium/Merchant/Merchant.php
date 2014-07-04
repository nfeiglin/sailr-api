<?php
namespace Sailr\Emporium\Merchant;

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



class Merchant implements MerchantInterface{


    protected $apiMode = '';
    protected $product;
    protected $buyer;
    protected $initialInput;

    protected $sellerDisplayName = '';
    public $rules = [
        'country' => 'required|countryCode',
        'street_number' => 'required',
        'street_name' => 'required',
        'city' => 'required',
        'state' => 'required',
        'zipcode' => 'required'
    ];

    public function ___construct($apiMode = '', $sellerName = 'Sailr') {
        $this->apiMode = $apiMode;
        $this->sellerDisplayName = $sellerName;
    }

    public function setApiMode($mode) {
        $this->apiMode = $mode;
    }

    public function getApiMode() {
        return $this->apiMode;
    }

    public function setDisplaySellerName($sellerName) {
        $this->$sellerDisplayName = $sellerName;
    }

    public function getDisplaySellerName() {
        return $this->sellerDisplayName;
    }

    public function setPurchaseProduct(\Item $item) {
        $this->product = $item;
    }

    public function isProductPublic(\Item $item) {
        if ($item->public == 1) {
            return true;
        }

        else {
            return false;
        }
    }

    public function apiMode($mode = 'sandbox') {
        $this->setApiMode($mode);
        return $this;
    }


    public function product($product = null) {

        if(!isset($product)) {
            return $this;
        }

        else {
            if (is_object($product)) {
                $this->product = $product;
            }

            else {
                $this->product = \Item::where('id', '=', $product)->with('User')->firstOrFail();
            }

        }
        return $this;

    }

    public function setBuyer($user) {
        $this->buyer = $user;
    }

    public function getBuyer() {
        return $this->buyer;
    }

    public function withBuyer($user) {
        if (isset($this->buyer) && !isset($user)) {
            return $this;
        }

        else if(isset($user)) {
            $this->setBuyer($user);
        }

        return $this;
    }

    public function withInitialInput($array) {
        $this->initialInput = $array;
        return $this;
    }
    public function setupPurchase($item, $buyerObject, $postInput) {
        $input = $postInput;


        if (!strlen($item->user->email) > 0) {

            throw new \Exception("The user relationship has not been included on the item model ");
        }

        $shippingFee = $item->ship_price;

        //Store some initial info in the database
        $checkout = new \Checkout;
        $checkout->item_id = $item->id;
        $checkout->user_id = $buyerObject->id;
        $checkout->completed = 0;
        $checkout->save();

        //$checkoutSaved = Checkout::find($checkout->id);

        //Calculate the amount the needs to be paid
        $total = floatval($item->price) + $shippingFee;

        $config = \Config::get("paypal.$this->apiMode");

        $baseURL = \URL::to('/');
        $returnURL = $baseURL . '/buy/' . $checkout->id . '/confirm';
        $cancelURL = $baseURL . '/buy/' . $checkout->id . '/cancel';

        if ($config['mode'] == 'live') {
            $returnURL = \URL::action('BuyController@showConfirm', $checkout->id);
            $cancelURL = \URL::action('Controller@cancel', $checkout->id);
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
        $paypalItem->Description = str_limit($item->title);
        $paypalItem->Name = str_limit($item->title, 124);
        $paypalItem->Quantity = 1;
        $paypalItem->Number = $item->id;
        $paypalItem->ItemURL = URL::action('BuyController@create', [$item->user->username, $item->id]);
        $paypalItem->ItemCategory = 'Physical';

        $paymentDetails1->PaymentDetailsItem = $paypalItem;

        // A unique identifier of the specific payment request, which is
        // required for parallel payments.
        $paymentDetails1->PaymentRequestID = sha1(microtime());

        $shipToAddress1 = new AddressType();
        $shipToAddress1->Street1 = $address1;
        $shipToAddress1->CityName = $input['city'];
        $shipToAddress1->StateOrProvince = $input['state'];
        $shipToAddress1->Country = $input['country'];
        $shipToAddress1->PostalCode = $input['zipcode'];

        // Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
        $paymentDetails1->NotifyURL = \URL::action('ipn');

        $paymentDetails1->ShipToAddress = $shipToAddress1;

        $setExpressCheckoutRequestDetails->AllowNote = 1;
        $setExpressCheckoutRequestDetails->BrandName = $this->getDisplaySellerName();

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


        if ($response->Ack == "Success") {


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

            return Redirect::to('/')->with('fail', 'Paypal has encountered an error');
            //dd($response);
        }

    }
} 