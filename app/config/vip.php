<?php
return [
    'plans' => [
        'awesome' => [
            'count' => [
                'product' => [
                    'create' => [
                        'db' => DB::table('items'),
                        'max' => 999999999999999
                    ]
                ]

            ]
        ],

        'default' => [
            'count' => [
                'product' => [
                    'create' => [
                        'db' => DB::table('items'),
                        'max' => 4
                    ]
                ]
            ]
        ]
    ]
];
