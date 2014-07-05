<?php
namespace Sailr\Validators\Exceptions;

class ValidatorException extends \Exception {

    public static function make(\Validator $validator) {
        $validatorException = new static;
        $validatorException->setValidator($validator);
        return $validatorException;
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