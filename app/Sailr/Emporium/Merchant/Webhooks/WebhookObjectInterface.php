<?php

namespace Sailr\Emporium\Merchant\Webhooks;


interface WebhookObjectInterface
{
    public function getProduct();
    public function getTransactionIdentifier();

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
