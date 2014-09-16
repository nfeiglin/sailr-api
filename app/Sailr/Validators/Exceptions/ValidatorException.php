<?php
namespace Sailr\Validators\Exceptions;
use Illuminate\Validation\Validator;

class ValidatorException extends \Exception {

    /**
     * @var $validator Validator the validator instance on which the exception was thrown
     */
    protected $validator;

    public function __construct(Validator $validator, $message = "", $code = 0, Exception $previous = null) {
        $this->validator = $validator;
    }
    public static function make(Validator $validator) {
        $validatorException = new static;
        $validatorException->setValidator($validator);
        return $validatorException;
    }

    public function getValidator() {
        return $this->validator;
    }

    public function setValidator(Validator $validator) {
        $this->validator = $validator;
    }

    public function getErrorMessages() {
        return $this->validator->messages()->toArray();
    }
} 