<?php
return [


    'buyer' => [

        'pending' => [
            'address' => "The payment is pending because you did not include a confirmed shipping address and the seller's PayPal Payment Receiving Preferences are set to allow them to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your PayPal Profile.",
            'echeck' => "The payment is pending because it was made by an eCheck that has not yet cleared.",
            'intl' => "The payment is pending because you hold a non-U.S. PayPal account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your PayPal Account Overview.",
            'multi-currency' => "You do not have PayPal balance in the currency sent, and you do not have your PayPal profile's Payment Receiving Preferences option set to automatically convert and accept this payment. As a result, you must manually accept or deny this payment.",
            'paymentreview' => "The payment is pending while it is reviewed by PayPal for risk.",
            'regulatory_review' => "The payment is pending because PayPal is reviewing it for compliance with government regulations. PayPal say they will complete this review within 72 hours.",
            'unilateral' => "The PayPal payment is pending because it was made to an email address that is not yet registered or confirmed. Does your Sailr account email match with your PayPal account email? Please contact the buyer to cancel or arrange alternate payment arrangements.",
            'upgrade' => "The PayPal payment is pending because it was made via credit card and you must upgrade your PayPal account to Business or Premier status before you can receive the funds. This can also mean that you have reached the monthly limit for transactions on your PayPal account.",
            'other' => "The PayPal payment is pending for a reason we don't know. Please check your PayPal account and make contact with the buyer as necessary"

        ],

        'error' => [
            'failed' => 'The PayPal payment has failed. Please check your PayPal account for more information and make contact with the seller if necessary',
            'denied' => 'The PayPal payment has been denied. Please check your PayPal account for more information and contact the seller if necessary',
            'expired' => 'The PayPal payment authorization has expired so the payment could not be processed, contact the seller if necessary',
            'default' => "There's been an error with the PayPal payment. Please check your PayPal account for more information and contact the seller if necessary"
        ],

    ],


    'seller' => [

        'pending' => [
            'address' => "The payment is pending because your customer did not include a confirmed shipping address and your PayPal Payment Receiving Preferences is set to allow you to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your PayPal Profile.",
            'echeck' => "The payment is pending because it was made by an eCheck that has not yet cleared.",
            'intl' => "The payment is pending because you hold a non-U.S. PayPal account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your PayPal Account Overview.",
            'multi-currency' => "You do not have PayPal balance in the currency sent, and you do not have your PayPal profile's Payment Receiving Preferences option set to automatically convert and accept this payment. As a result, you must manually accept or deny this payment.",
            'paymentreview' => "The payment is pending while it is reviewed by PayPal for risk.",
            'regulatory_review' => "The payment is pending because PayPal is reviewing it for compliance with government regulations. PayPal say they will complete this review within 72 hours.",
            'unilateral' => "The PayPal payment is pending because it was made to an email address that is not yet registered or confirmed. Does your Sailr account email match with your PayPal account email? Please contact the buyer to cancel or arrange alternate payment arrangements.",
            'upgrade' => "The PayPal payment is pending because it was made via credit card and you must upgrade your PayPal account to Business or Premier status before you can receive the funds. This can also mean that you have reached the monthly limit for transactions on your PayPal account.",
            'other' => "The PayPal payment is pending for a reason we don't know. Please check your PayPal account and make contact with the buyer as necessary"

        ],

        'error' => [
            'failed' => 'The PayPal payment has failed. Please check your PayPal account for more information and make contact with the buyer if necessary',
            'denied' => "The buyer's PayPal payment has been denied. Contact the buyer if necessary",
            'expired' => "The buyer's PayPal payment authorization has expired so the payment could not be processed, contact the buyer if necessary",
            'default' => "There's been an error with the PayPal payment from the buyer. Please check your PayPal account for more information and contact the buyer if necessary"
        ],

    ]

];