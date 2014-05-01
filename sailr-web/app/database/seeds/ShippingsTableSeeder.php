<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ShippingsTableSeeder extends Seeder {

	public function run()
	{
		$faker = Faker::create();

		foreach(range(1, 10) as $index)
		{
			$input = [
				'domestic_shipping_price' => 7.99,
				'domestic_shipping_desc' => 'Will ship everywhere in Australia',
				'international_shipping_price' => 14.99,
				'international_shipping_desc' => 'Will ship to UK and US'

			];

			$item_id = $index;
			$itemcontroller = new ItemsController();
			$itemcontroller->doShippingFromInput($input, $item_id);
		}
	}

}