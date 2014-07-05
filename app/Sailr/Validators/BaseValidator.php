<?php namespace Sailr\Validators;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;


class BaseValidator implements ValidatorInterface {

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

    public function getErrorMessages() {
        return $this->errors;
    }

    public function getValidator() {
        return $this->validator;
    }
} 