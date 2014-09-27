<?php
use Faker\Factory as Faker;

class ItemsTableSeeder extends Seeder
{


    public function getRandomLoremPixleString() {
        $options = [
            '',
            'people',
            'food',
            'city',
            'sports',
            'nature',
            'cats',
            'business',
            'transport',
            'animals',
            'fashion',
            'nightlife'

        ];

        return $options[array_rand($options)];
    }
    public function run()
    {
        /*
        $faker = Faker::create();

        // Uncomment the below to wipe the table clean before populating
        // DB::table('items')->truncate();
        foreach (range(1, 12) as $index) {
            Item::create([
                'user_id' => $index,
                'description' => '<h2>Heading></h2><p>An incredible, amazing, cool, life-changing description goes here!</p> test ' . $index,
                'title' => $faker->colorName . ' ' . $faker->city . ' ' . $faker->country ,
                'currency' => 'AUD',
                'initial_units' => $index,
                'price' => $faker->randomFloat(2, 2.00, 200.00),
                'ships_to' => 'AU'

            ]);

            Item::create([
                'user_id' => $index,
                'description' => '<h2>Heading></h2><p>An incredible, amazing, cool, life-changing description goes here!</p> test ' . $index,
                'title' => $faker->colorName . ' ' . $faker->city . ' ' . $faker->country ,
                'currency' => 'AUD',
                'initial_units' => $index,
                'price' => $faker->randomFloat(2, 2.00, 200.00),
                'ships_to' => 'AU'

            ]);

            Item::create([
                'user_id' => $index,
                'description' => '<h2>Heading></h2><p>An incredible, amazing, cool, life-changing description goes here!</p> test ' . $index,
                'title' => $faker->colorName . ' ' . $faker->city . ' ' . $faker->country ,
                'currency' => 'AUD',
                'initial_units' => $index,
                'price' => $faker->randomFloat(2, 2.00, 200.00),
                'ships_to' => 'AU'

            ]);

            Item::create([
                'user_id' => $index,
                'description' => '<h2>Heading></h2><p>An incredible, amazing, cool, life-changing description goes here!</p> test ' . $index,
                'title' => $faker->colorName . ' ' . $faker->city . ' ' . $faker->country ,
                'currency' => 'AUD',
                'initial_units' => $index,
                'price' => $faker->randomFloat(2, 2.00, 200.00),
                'ships_to' => 'AU'

            ]);




        }
*/
        foreach (Item::all() as $item) {

            $set_id = sha1(microtime());

            foreach(range(1, 3) as $index) {
                Photo::create([
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                    'type' => 'full_res',
                    'set_id' => $set_id,
                    'url' => 'http://lorempixel.com/640/640/' . $this->getRandomLoremPixleString()
                ]);

                Photo::create([
                    'user_id' => $item->user_id,
                    'item_id' => $item->id,
                    'type' => 'thumbnail',
                    'set_id' => $set_id,
                    'url' => 'http://lorempixel.com/150/150/' . $this->getRandomLoremPixleString()
                ]);
            }
          
        }


    }

}
