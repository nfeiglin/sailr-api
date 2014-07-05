<?php


namespace Sailr\Emporium\Merchant\Entity;


class BaseAddressEntity {

    protected $data;

    public static function make($data) {
        $object = new static;
        $object->data = $data;
        return $object;
    }


} 