<?php

namespace Sailr\Currencyvalidator;


class CurrencyValidator extends Illuminate\Validation\Validator {
    public function validateCurrency($attribute, $value, $parameters) {

        $codes = [
            'USD',
            'AUD',
            'CAD',
            'GBP',
            'EUR',
            'CHF',
            'PLN',
            'CZK',
            'DKK',
            'NOK',
            'SEK',
            'ILS',
            'HKD',
            'PHP',
            'NZD',
            'RUB',
            'THB',
            'SGD',
            'MXN',
        ];

         return in_array($attribute, $codes, true);
    }
} 