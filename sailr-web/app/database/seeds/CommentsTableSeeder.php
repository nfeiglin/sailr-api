<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class CommentsTableSeeder extends Seeder {

	public function run()
	{
		Comment::truncate();
		$faker = Faker::create();

	foreach (User::all() as $user) {

			foreach(Item::all() as $item)
			{
				Comment::create([
					'item_id' => $item->id,
					'user_id' => $user->id,
					'comment' => 'Srsly an amazing comment my gawd'
				]);

			}
		}

	}

}