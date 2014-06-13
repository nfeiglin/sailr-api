<?php
return [
  'plans' => [
    'awesome' => [
        'count' => [
            'product.create' => [
                'db' => DB::table('items'),
                'max' => 40
            ]
        ]
    ],

    'default' => [
         'count' => [
             'product' => [
                 'create' => [
                     'db' => function() {
                         return DB::table('items');
                     },
                     'max' => 4
                 ]
             ]
         ]
    ]
  ]
];