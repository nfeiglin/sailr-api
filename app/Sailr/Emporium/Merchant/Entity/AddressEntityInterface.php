<?php

namespace Sailr\Emporium\Merchant\Entity;


interface AddressEntityInterface {
    /* Shipping related functions */
    public function getShipToName();
    public function getAddress1();
    public function getAddress2();
    public function getCity();
    public function getState();
    public function getCountry();
    public function getCountryCode();
    public function getZipCode();

} 