<?php
namespace Sailr\Validators\Exceptions;

class ValidatorException extends \Exception {

    public function ___construct() {

    }

    protected $validator;


    public function getValidator() {
        return $this->validator;
    }

    public function setValidator(\Validator $validator) {
        $this->validator = $validator;
    }

    public function getErrorMessages() {
        return $this->validator->messages()->toArray();
    }
} 