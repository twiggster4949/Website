<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchingNowTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watching_now', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer("media_item_id")->unsigned();
			$table->string("session_id", 255);
			$table->boolean("playing")->default(true);
			$table->timestamp("last_play_time")->nullable();
			$table->timestamps();
			
			$table->foreign("media_item_id")->references('id')->on('media_items')->onUpdate("restrict")->onDelete('cascade');
			$table->foreign("session_id")->references('id')->on('sessions')->onUpdate("restrict")->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watching_now');
	}

}
