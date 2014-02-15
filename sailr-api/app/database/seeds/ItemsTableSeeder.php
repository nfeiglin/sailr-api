<?php

class ItemsTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		// DB::table('items')->truncate();

		$items = array(
            array(
                'user_id' => '1',
                'description' => 'Amazing new hand bag. Ships in 3 days in Australia. Great condition. Bright red! Amazing gift.',
                'quantity' => 1
            ),

            array(
                'user_id' => '1',
                'description' => 'Apple 11 inch Macbook Air 2012 edition',
                'quantity' => 1,
                'price' => 999.99
            ),
        );

		// Uncomment the below to run the seeder
		// DB::table('items')->insert($items);
	}

}
