<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('title', 255);
			$table->mediumText('description')->nullable();
			$table->string('currency', 3);
			$table->decimal('price', 8, 2);
			$table->decimal('ship_price', 8, 2)->string(2);
            $table->string('ships_to', 2);
			$table->integer('initial_units')->unsigned();
			$table->boolean('public')->default(0);
			$table->timestamps();
            $table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('items');
	}

}
