<?php
return array(

    'sandbox' => array(
        'mode' => 'sandbox',
        'acct1.UserName' => getenv('PAYPAL_SANDBOX_USERNAME'),
        'acct1.Password' => getenv('PAYPAL_SANDBOX_PASSWORD'),
        'acct1.Signature' => getenv('PAYPAL_SANDBOX_SIGNATURE'),
        'acct1.AppId' => getenv('PAYPAL_SANDBOX_APPID'),
    ),

    'live' => array(
        'mode' => 'LIVE',
        'acct1.UserName' => getenv('PAYPAL_LIVE_USERNAME'),
        'acct1.Password' => getenv('PAYPAL_LIVE_PASSWORD'),
        'acct1.Signature' => getenv('PAYPAL_LIVE_SIGNATURE'),
        'acct1.AppId' => getenv('PAYPAL_LIVE_APPID'),

    )



);