<?php
/**
 * Created by PhpStorm.
 * User: feiglin nathan
 * Date: 17/09/14
 * Time: 12:02 PM
 */

namespace Sailr\Validators;


class ItemsValidator extends BaseValidator implements ValidatorInterface {
    protected $rules = [
        'create' => [
            'title' => 'required|max:255',
            'currency' => 'required|currency',
            'price' => 'required|numeric|max:999999'
        ],

        'update' => [
            'title' => ['sometimes', 'max:255'],
            'price' => ['sometimes', 'min:0', 'max:999999', 'numeric'],
            'ship_price' => ['sometimes', 'min:0', 'max:999999', 'numeric'],
            'currency' => ['sometimes', 'currency'],
            'ships_to' => ['sometimes', 'countryCode'],
        ],

        'publish' => [
            'title' => ['required', 'max:255'],
            'price' => ['required', 'min:0', 'max:999999', 'numeric'],
            'ship_price' => ['required', 'min:0', 'max:999999', 'numeric'],
            'currency' => ['required', 'currency'],
            'ships_to' => ['required', 'countryCode'],

        ]
    ];
} 