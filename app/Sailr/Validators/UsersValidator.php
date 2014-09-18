<?php
/**
 * Created by PhpStorm.
 * User: feiglin nathan
 * Date: 17/09/14
 * Time: 12:02 PM
 */

namespace Sailr\Validators;


class UsersValidator extends BaseValidator implements ValidatorInterface {
    protected $rules = [
        'create' => [
            'terms_of_service' => 'sometimes|accepted',
            'name' => 'required|min:2|max:99',
            'email' => 'required|email|max:99|unique:users,email',
            'username' => 'required|alpha_dash|max:99|unique:users,username',
            'password' => 'required|min:6',
            'bio' => 'sometimes|max:240'
        ],

        'update' => [
            'name' => 'sometimes|min:2|max:99',
            'email' => 'sometimes|email|max:99|unique:users,email',
            'username' => 'sometimes|alpha_dash|max:99|unique:users,username',
            'password' => 'sometimes|min:6',
            'bio' => 'sometimes|max:240'
        ],

    ];
} 