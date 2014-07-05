<?php

namespace Sailr\Validators;


class PurchaseValidator extends BaseValidator implements ValidatorInterface {
    protected $rules = [
      'create' => [
          'country' => 'required|countryCode',
          'street_number' => 'required',
          'street_name' => 'required',
          'city' => 'required',
          'state' => 'required',
          'zipcode' => 'required',
          'product' => 'publicProduct|inStock|hasUserRelationship|notSellersProduct'
      ],

        'confirmPurchase' => [
            'product' => 'publicProduct|hasUserRelationship|notSellersProduct|inStock'
        ],

        'doPurchase' => [
            'product' => 'publicProduct|notSellersProduct|inStock'
        ],


    ];
} 