<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTxnidToCheckoutsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('checkouts', function(Blueprint $table)
		{
			$table->string('txn_id')->nullable()->after('ack');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('checkouts', function(Blueprint $table)
		{
			$table->dropColumn('txn_id');
		});
	}

}
