<?php
use Faker\Factory as Faker;

class ItemsTableSeeder extends Seeder
{


    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('items')->truncate();
        $faker = Faker::create();
        foreach (range(1, 10) as $index) {
            Item::create([
                  'user_id' => $index,
                  'description' => 'An incredible, amazing, cool, life-changing description goes here! ' . $index,
                  'title' => 'A life-changing title goes here!',
                  'currency' => 'AUD',
                  'initial_units' => $index,
                  'price' => $faker->randomFloat(2, 2.00, 200.00)

              ]);

        }


    }

}
