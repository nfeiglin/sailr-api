<?php

namespace Sailr\Emporium\Merchant;


use PayPal\Service\PayPalAPIInterfaceServiceService;
use Sailr\Emporium\Merchant\Entity\AddressEntityInterface;

trait MerchantGetterAndSetterTrait {
    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    public function setRedirectUrl($url) {
        $this->redirectUrl = $url;
    }
    public function setApiMode($mode = 'sandbox') {
        $this->apiMode = $mode;
    }

    public function getApiMode() {
        return $this->apiMode;
    }


    public function setConfig($config) {
        $this->createPayPalApiServiceWrapper($config);
        $this->config = $config;
    }

    public function getConfig() {
        return $this->config;
    }

    public function config($config = null) {
        if (isset($config)) {
            $this->setConfig($config);
            return $this;
        }

        else {
            return $this->getConfig();
        }
    }

    public function setDisplaySellerName($sellerName) {
        $this->sellerDisplayName = $sellerName;
    }

    public function getDisplaySellerName() {
        return $this->sellerDisplayName;
    }

    public function setPurchaseProduct(\Item $item) {
        $this->product = $item;
    }

    public function product($product = null) {

        if(!isset($product)) {
            return $this->product;
        }

        else {
            if (is_object($product)) {
                $this->setProduct($product);
            }

            else {
                $this->setProduct(\Item::where('id', '=', $product)->with('User')->firstOrFail());
            }

        }
        return $this;

    }

    public function apiMode($mode = null) {
        if (!isset($mode)) {
            return $this->getApiMode();
        }

        else {
            $this->setApiMode($mode);
            return $this;
        }
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct($item) {
        $this->product = $item;
    }

    public function setBuyer($user) {
        $this->buyer = $user;
    }

    public function getBuyer() {
        return $this->buyer;
    }

    public function buyer($user = null) {
        if (isset($user)) {
            $this->setBuyer($user);
            return $this;
        }

        else {
            return $this->getBuyer();
        }



    }

    public function withSellerDisplayName($displayName) {
        $this->setDisplaySellerName($displayName);
        return $this;
    }

    public function withAddress(AddressEntityInterface $addressEntity) {
        $this->setAddress($addressEntity);
        return $this;
    }

    public function setAddress(AddressEntityInterface $address) {
        $this->address = $address;
    }

    /**
     * @return AddressEntityInterface
     *
     */
    public function getAddress() {
        return $this->address;
    }

    public function address($address = null) {
        if (!isset($address)) {
            return $this->getAddress();
        }

        else {
            $this->setAddress($address);
            return $this;
        }
    }

    public function getPaypalToken() {
        return $this->paypalToken;
    }

    public function setPaypalToken($token) {
        $this->paypalToken = $token;
    }

    public function withPaypalToken($token) {
        $this->setPaypalToken($token);
        return $this;
    }

    public function getPayerId() {
        return $this->payerId;
    }

    public function setPayerId($payerId) {
        $this->payerId = $payerId;
    }

    public function withPayerId($payerId) {
        $this->setPayerId($payerId);
        return $this;
    }

    public function setPaymentDetails($paymentDetailsObject) {
        $this->paymentDetails = $paymentDetailsObject;
    }

    public function getPaymentDetails() {
        return $this->paymentDetails;
    }

    public function getCheckout() {
        return $this->checkout;
    }

    public function setCheckout(\Checkout $checkout) {
        $this->checkout = $checkout;
    }

    public function withCheckout(\Checkout $checkout) {
        $this->setCheckout($checkout);
        return $this;
    }

    public function webhookUrl($url = null) {
        if(isset($url)) {
            $this->webhookUrl = $url;
            return $this;
        }

        else {
            return $this->webhookUrl;
        }
    }

    public function returnUrl($url = null) {
        if (isset($url)) {
            $this->returnUrl = $url;
            return $this;
        }

        else {
            return $this->returnUrl;
        }
    }

    public function cancelUrl($url = null) {
        if (isset($url)) {
            $this->cancelUrl = $url;
            return $this;
        }

        else {
            return $this->cancelUrl;
        }
    }

    public function redirectEntity(\Sailr\Entity\RedirectEntity $entity = null) {
        if (isset($entity)) {
            $this->redirectEntity = $entity;
            return $this;
        }

        else {
            return $this->redirectEntity;
        }


    }
} 