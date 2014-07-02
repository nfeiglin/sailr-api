<?php

namespace Sailr\Emporium\Merchant\Webhooks;


interface WebhookObjectInterface
{
    public function getProduct();
    public function getTransactionIdentifier();

    /* Shipping releated functions */
    public function getShipToName();
    public function getAddress1();
    public function getCity();
    public function getState();
    public function getCountry();
    public function getCountryCode();
    public function getZipCode();

    /* Price related stuff */
    public function getGrossAmount();
    public function getNetPaidToSeller();
    public function getPaymentProcessingFees();
    public function getCurrencyCode();
    public function getShippingPrice();
    public function getProductPrice();

    /* Other goodies */
    public function getBuyersNote();


}
