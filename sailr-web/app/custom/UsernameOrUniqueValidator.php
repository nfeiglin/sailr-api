<?php
class UsernameOrUniqueValidator extends Illuminate\Validation\Validator {
    public function validateUsernameOrUnique ($attribute, $value, $parameters) {
        if($value == Auth::user()->username) {
            return true;
        }

        $countOfDesiredUsername = User::where('username', '=', $value)->count();

        if ($countOfDesiredUsername > 0) {
            return false;
        }

        else {
            return true;
        }
    }
}

