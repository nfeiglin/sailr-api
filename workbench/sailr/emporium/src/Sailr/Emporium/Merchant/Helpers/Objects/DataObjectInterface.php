<?php

namespace Sailr\Emporium\Merchant\Helpers\Objects;


interface DataObjectInterface {

    public static function make($object);
    public function getData();
} 