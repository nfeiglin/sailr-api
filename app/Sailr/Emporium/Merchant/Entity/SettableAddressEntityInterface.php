<?php

namespace Sailr\Emporium\Merchant\Entity;


interface SettableAddressEntityInterface {

    public function setShipToName($name);
    public function setAddress1($address);
    public function setAddress2($address);
    public function setCity($city);
    public function setState($state);
    public function setCountry($countryName);
    public function setCountryCode($iso2DigitCountryCode);
    public function setZipCode($zipCode);

} 