<?php

namespace Sailr\Emporium\Merchant\Webhooks;


use \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder;
use Sailr\Emporium\Merchant\Entity\AddressEntityInterface;

class PaypalWebhook implements WebhookObjectInterface, AddressEntityInterface {

    /** A webhook object implementation for appeasing the views: 'Sailr\Emporium\Webhooks\PaypalWebhook'
     * @property \LogicalGrape\PayPalIpnLaravel\Models\IpnOrder $ipn
     *
     */
    protected $ipn;


    public function __construct(IpnOrder $IPNModel) {
        $this->ipn = $IPNModel;

    }

    public static function make(IpnOrder $ipnModel) {
        return new static($ipnModel);
    }

    public function setIPNModel($IPNModel) {
        $this->ipn = $IPNModel;
    }

    public function getProduct() {
        return \Checkout::where('txn_id', '=', $this->getTransactionIdentifier())->firstOrFail(['id', 'item_id'])->item();
    }

    public function getTransactionIdentifier() {
        return $this->ipn->txn_id;
    }


    /* Shipping related functions */
    public function getShipToName() {
        return $this->ipn->address_name;
    }
    public function getAddress1() {
        return $this->ipn->address_street;
    }

    public function getAddress2() {
        return '';
    }

    public function getCity() {
        return $this->ipn->address_city;
    }
    public function getState() {
        return $this->ipn->address_state;
    }
    public function getCountry() {
        return $this->ipn->address_country;
    }

    public function getCountryCode() {
        return $this->ipn->address_country_code;
    }

    public function getZipCode() {
        return $this->ipn->address_zip;
    }

    public function getCurrencyCode() {
        return $this->ipn->mc_currency;
    }
    /* Price related stuff */
    public function getGrossAmount() {
        return $this->ipn->mc_gross;
    }
    public function getNetPaidToSeller() {
        return floatval($this->ipn->mc_gross) - floatval($this->ipn->mc_fee);
    }
    public function getPaymentProcessingFees() {
        return $this->ipn->mc_fee;
    }
    public function getShippingPrice() {
        return $this->ipn->mc_shipping;
    }

    public function getProductPrice() {
        return $this->getProduct()->price;
    }

    public function getBuyersNote() {
        if(isset($this->ipn->memo)) {
            return $this->ipn->memo;
        }

        else {
            return false;
        }
    }

} 