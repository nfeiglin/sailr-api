<?php


namespace Sailr\Emporium\Merchant\Entity;


class PayPalAddressEntity extends BaseAddressEntity implements AddressEntityInterface {


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
        return $this->data>StateOrProvince;
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

} 