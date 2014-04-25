<?php
use Faker\Factory as Faker;

class ItemsTableSeeder extends Seeder
{


    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('items')->truncate();
      /*
        foreach (range(1, 11) as $index) {
            Item::create([
                  'user_id' => $index,
                  'description' => 'An incredible, amazing, cool, life-changing description goes here! ' . $index,
                  'title' => 'A life-changing title goes here!',
                  'currency' => 'AUD',
                  'initial_units' => $index,
                  'price' => $faker->randomFloat(2, 2.00, 200.00)

              ]);

        }

        */

        foreach (Item::all() as $item) {

          Photo::create([
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                    'type' => 'full_res',
                    'url' => 'http://sailr.web/img/default-lg.jpg'
                ]);
          
        }


    }

}
