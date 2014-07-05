<?php

namespace Sailr\Emporium\Merchant\Exceptions;

class ProductNotPublicException extends \Exception {

    protected $code = 403;
    protected $message = 'The product is not public';   // exception message

} 