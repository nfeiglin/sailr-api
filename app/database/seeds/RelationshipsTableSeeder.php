<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class RelationshipsTableSeeder extends Seeder
{

    public function run()
    {
        //DB::table('relationships')->truncate();
        $faker = Faker::create();

        foreach (range(1, 19) as $index) {
            $plusOne = $index + 1;
            Relationship::create([
                'user_id' => $index,
                'follows_user_id' => $plusOne
            ]);

            Relationship::create([
                'user_id' => $index,
                'follows_user_id' => $index + 2
            ]);

            Relationship::create([
                'user_id' => $index,
                'follows_user_id' => $index + 3
            ]);

            Relationship::create([
                'user_id' => $index,
                'follows_user_id' => $index + 4
            ]);

        }


    }
}