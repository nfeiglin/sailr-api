<?php
return array(
    'sandbox' => [
        'secret' => getenv('STRIPE_SANDBOX_SECRET'),
        'publishable' => getenv('STRIPE_SANDBOX_PUBLISHABLE'),
    ],

    'live' => [
        'secret' => getenv('STRIPE_LIVE_SECRET'),
        'publishable' => getenv('STRIPE_LIVE_PUBLISHABLE'),
    ]

);