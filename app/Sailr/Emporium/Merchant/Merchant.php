<?php
namespace Sailr\Emporium\Merchant;

use PayPal\EBLBaseComponents\PayerInfoType;
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
use Sailr\Emporium\Merchant\Entity\PayPalAddressEntity;
use Sailr\Emporium\Merchant\Exceptions\PayPalApiErrorException;
use Sailr\Emporium\Merchant\Exceptions\TokenDoesNotMatchLoggedInAccountException;
use Sailr\Emporium\Merchant\Exceptions\PayPalSessionExpiredException;
use Sailr\Validators\Exceptions\ValidatorException;
use Sailr\Validators\PurchaseValidator;
use Sailr\Emporium\Merchant\Entity\AddressEntityInterface;

class Merchant implements MerchantInterface {
    use MerchantGetterAndSetterTrait;

    /**
     * @var string $apiMode
     * @var \Item $product the product to be purchased
     * @var \User $buyer the buyer of the product
     * @var array $initialInput An array containing the address details of the buyer
     * @var \Sailr\Validators\PurchaseValidator The purchase validator instance
     * @var array The configuration array containing the API keys etc for PayPal
     * @var string $sellerDisplayName the title at the top of the PayPal payment page
     * @var AddressEntityInterface $address An address entity object of shipping address
     * @var \Checkout the checkout model for the transaction
     */

    protected $apiMode = '';
    protected $product;
    protected $buyer;
    protected $initialInput;
    protected $redirectUrl = '';
    protected $validator;
    protected $config;
    protected $sellerDisplayName = '';
    protected $address;
    protected $paypalToken;
    protected $payerId;
    protected $paymentDetails;
    protected $checkout;


    public function ___construct($apiMode = 'sandbox', $sellerName = 'Sailr', PurchaseValidator $validator = null) {
        $this->apiMode = $apiMode;
        $this->sellerDisplayName = $sellerName;

        if (!isset($validator)) {
            $this->validator =  new PurchaseValidator;

        }

    }

    public function isProductPublic(\Item $item) {
        if ($item->public == 1) {
            return true;
        }

        else {
            return false;
        }
    }

    public function setupPurchase($item = null, $buyerObject= null, $postInput = null) {
        $input = $this->initialInput;
        $buyerObject = $this->buyer;
        $item = $this->getProduct();


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

        $config = $this->getConfig();

        $baseURL = \URL::to('/');
        $returnURL = $baseURL . '/buy/' . $checkout->id . '/confirm';
        $cancelURL = $baseURL . '/buy/' . $checkout->id . '/cancel';

        if ($config['mode'] == 'live') {
            $returnURL = \URL::action('BuyController@showConfirm', $checkout->id);
            $cancelURL = \URL::action('BuyController@cancel', $checkout->id);
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
        $paypalItem->ItemURL = \URL::action('BuyController@create', [$item->user->username, $item->id]);
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

            if ($config['mode'] == 'live') {
                $this->setRedirectUrl('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->Token);
                return Redirect::to();
            } else {
                $this->setRedirectUrl('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->Token);
            }


        } // ### Error Values
        // Access error values from error list using getter methods
        else {
            Log::error("API Error Message : " . $response->Errors[0]->LongMessage);
            throw new PayPalApiErrorException($response->Errors[0]->LongMessage);
        }

        return $this;

    }

    public function getConfirmationDetails() {
        $config = $this->getConfig();
        $input = Input::all();
        $paypalToken = $this->getPaypalToken();

        //Validate that the user is getting their own transaction not someone else's!
        $checkout = $this->getCheckout();
        $checkout->payerID = $this->getPayerId();
        $checkout->save();


        if ($checkout->token != $paypalToken | $checkout->user_id != Auth::user()->id) {
            throw new TokenDoesNotMatchLoggedInAccountException;
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
                throw new PayPalSessionExpiredException;
            }
           throw new PayPalApiErrorException;
        }

        $address = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->Address;
        $addressEntity = PayPalAddressEntity::make($address);
        $payment = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails;

        $this->setAddress($addressEntity);
        $this->setPaymentDetails($payment);

        return $this;

    }

    public function doPurchaseProduct() {
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

        $item = Item::where('id', '=', $checkout->item_id)->firstOrFail(['id', 'initial_units', 'public']);
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

        $result = $this->validator->validate(['product' => $item], 'doPurchase');

        if (!$result) {
            $ve = new ValidatorException;
            $ve->setValidator($this->validator->getValidator());
            throw $ve;
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
} 