<?php

// Composer: "fzaninotto/faker": "v1.4.0"
use Faker\Factory as Faker;

class CollectionsTableSeeder extends Seeder {


	public function run()
	{
        $faker = Faker::create();

        /*
		foreach(range(1, 10) as $index)
		{
			Collection::create([
                'user_id' => '12',
                'title' => $faker->firstName . 'collection'
			]);
		}
        */
        $items = Item::all();
        $collection = Collection::find(1);

        foreach($items as $item) {
            $collection->items()->attach($item);
        }
	}

}
