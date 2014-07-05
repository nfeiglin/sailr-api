<?php


namespace Sailr\Emporium\Merchant\Entity;


class PayPalAddressEntity extends BaseAddressEntity implements AddressEntityInterface, SettableAddressEntityInterface {


    public function getShipToName() {
        return $this->data->Name;
    }
    public function getAddress1() {
        return $this->data->Street1;
    }
    public function getAddress2() {
        if(isset($this->data->Street2)) {
            return $this->data->Street2;
        }
        else {
            return '';
        }
    }
    public function getCity() {
        return $this->data->CityName;
    }
    public function getState() {
        return $this->data->StateOrProvince;
    }
    public function getCountry() {
        return $this->data->CountryName;
    }
    public function getCountryCode() {
        return $this->data->Country;
    }
    public function getZipCode() {
        return $this->data->PostalCode;
    }

    public function setShipToName($name) {
        return $this->data->Name = $name;
    }
    public function setAddress1($address) {
        $this->data->Street1 = $address;
    }
    public function setAddress2($address) {
        $this->data->Street2 = $address;
    }
    public function setCity($city) {
        $this->data->CityName = $city;
    }
    public function setState($state) {
        $this->data->StateOrProvince = $state;
    }
    public function setCountry($countryName) {
        $this->data->CountryName = $countryName;
    }
    public function setCountryCode($iso2DigitCountryCode) {
        $this->data->Country = $iso2DigitCountryCode;
    }
    public function setZipCode($zipCode) {
       $this->data->PostalCode = $zipCode;
    }

} 