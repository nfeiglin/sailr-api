<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ProfileImgsTableSeeder extends Seeder
{

    public function run()
    {
        /*
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            ProfileImg::create([

            ]);
        }
        */

        $users = User::all();
        foreach ($users as $user) {
            ProfileImg::setDefaultProfileImages($user);
        }

    }

}