<?php
namespace Sailr\Validators;

interface ValidatorInterface {
    public function validate($data, $ruleset);
    public function getErrorMessages();
    public function getValidator();
}