<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class RelationshipsTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('relationships')->truncate();
        $faker = Faker::create();

        foreach (range(1, 19) as $index) {
            $plusOne = $index + 1;
            Relationship::create([
                'user_id' => $index,
                'follows_user_id' => $plusOne
            ]);
        }

        Relationship::create([
            'user_id' => 2,
            'follows_user_id' => 7
        ]);

        Relationship::create([
            'user_id' => 1,
            'follows_user_id' => 3
        ]);

        Relationship::create([
            'user_id' => 3,
            'follows_user_id' => 1
        ]);

        Relationship::create([
            'user_id' => 1,
            'follows_user_id' => 7
        ]);

        Relationship::create([
            'user_id' => 1,
            'follows_user_id' => 9
        ]);

        Relationship::create([
            'user_id' => 9,
            'follows_user_id' => 7
        ]);

        Relationship::create([
            'user_id' => 9,
            'follows_user_id' => 3
        ]);

        $nathan = User::where('username', '=', 'nfeiglin')->firstOrFail();

        foreach (range(1, 10) as $i) {
            Relationship::create([
                'user_id' => $nathan->id,
                'follows_user_id' => $i
                ]
            );

        }


    }
}