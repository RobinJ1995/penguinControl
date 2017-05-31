<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFtpUserFieldName extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		Schema::table ('ftp',
			function (Blueprint $table)
			{
				$table->renameColumn ('user', 'username'); // "user" conflicts with $ftp->uid <--> $user->uid relation //
			}
		);
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		Schema::table ('ftp',
			function (Blueprint $table)
			{
				$table->renameColumn ('username', 'user');
			}
		);
	}
}
