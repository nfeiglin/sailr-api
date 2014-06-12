<?php

namespace Sailr\Vip\Facades;
use Illuminate\Support\Facades\Facade;

class VIP extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'VIP';
    }

}
