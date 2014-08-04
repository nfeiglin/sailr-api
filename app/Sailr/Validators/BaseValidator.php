<?php namespace Sailr\Validators;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class BaseValidator implements ValidatorInterface {

    /**
     * @var array $rules Validation rules
     * @var array $messages Custom validation messages
     * @var \Illuminate\Validation\Validator $validator The validator instance
     * @var \Illuminate\Support\MessageBag $errors A messagebag object of the validation errors
     */
    protected $rules = [];
    protected $messages = [];
    protected $errors;
    protected $validator;

    public function validate($data, $ruleset = 'create') {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $rules = $this->rules[$ruleset];

        $messages = [];
        if (array_key_exists($ruleset, $this->messages)) {
            $messages = $this->messages[$ruleset];
        }

        $validator = Validator::make($data, $rules, $messages);
        if (!$result = $validator->passes()) {
            $this->errors = $validator->messages();
        }
        $this->validator = $validator;

        return $result;

    }

    /**
     * @return \Illuminate\Support\MessageBag
     */
    public function getErrorMessages() {
        return $this->errors;
    }

    /**
     * @return \Illuminate\Validation\Validator
     */
    public function getValidator() {
        return $this->validator;
    }

    /**
     * @return static A validator object
     */
    public static function make() {
        return new static;
    }
} 