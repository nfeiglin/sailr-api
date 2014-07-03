<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCheckoutsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('checkouts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('item_id');
			$table->unsignedInteger('user_id');
            $table->string('payerID')->nullable();
			$table->string('token', 30)->nullable();
			$table->string('ack', 30)->nullable();
            $table->string('txn_id')->nullable();
			$table->boolean('completed');
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
		Schema::drop('checkouts');
	}

}
