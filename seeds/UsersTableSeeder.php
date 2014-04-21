<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

                foreach(range(1, 10) as $index)
                {
                    $u = User::create([
                    'name' => $faker->name,
                    'username' => $faker->userName,
                    'email' => $faker->email,
                     'bio' =>  $faker->sentence(),
                    'password' => Hash::make('1231231231')
                    ]);

                    ProfileImg::setDefaultProfileImages($u);
                }

        $x = User::create([
            'name' => 'Nathan Feiglin',
            'username' => 'nfeiglin',
            'email' => 'nathan.f1234@gmail.com',
            'password' => Hash::make('1231231231')
        ]);

        ProfileImg::setDefaultProfileImages($x);
    }

}