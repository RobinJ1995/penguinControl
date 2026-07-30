<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes what the original deployment needed and a general-purpose panel does not.
 *
 * `user_info.schoolnr` held a student number, because members were students. Nothing
 * enforced a format -- the regex that did was commented out years ago -- and every screen
 * that showed it showed it beside the username, so it was an identity note for one
 * organisation rather than a field the panel does anything with.
 *
 * `user_log` was a billing ledger: one row per user per year, carrying "not to be
 * billed" / "to be billed" / "billed" and exported to CSV for whoever chased the
 * membership fee. It has no amounts, no currency and no plans, so it does not generalise
 * into billing -- it only records that somebody owed a yearly fee.
 *
 * `user.smb_lm` and `user.smb_nt` are Samba LM and NT password hashes. Nothing has
 * written a meaningful value to them in the lifetime of this repository -- the seeder
 * sets both to the empty string -- and LM hashes should not be stored by anything built
 * today.
 */
return new class extends Migration
{
	public function up (): void
	{
		Schema::dropIfExists ('user_log');

		Schema::table ('user_info', function (Blueprint $table)
		{
			$table->dropColumn ('schoolnr');
		});

		Schema::table ('user', function (Blueprint $table)
		{
			$table->dropColumn (['smb_lm', 'smb_nt']);
		});
	}

	public function down (): void
	{
		Schema::table ('user', function (Blueprint $table)
		{
			$table->string ('smb_lm', 255);
			$table->string ('smb_nt', 255);
		});

		Schema::table ('user_info', function (Blueprint $table)
		{
			$table->string ('schoolnr', 20);
		});

		// Recreated as the baseline migration had it //
		Schema::create ('user_log', function (Blueprint $table)
		{
			$table->integer ('id', true);
			$table->integer ('user_info_id');
			$table->timestamp ('time')->useCurrent ();
			$table->boolean ('new')->default (1);
			$table->tinyInteger ('status')->default (0)
				->comment ('-1 = Not to be billed // 0 = To be billed // 1 = Billed');

			$table->index ('user_info_id');
			$table->foreign ('user_info_id')->references ('id')->on ('user_info');
		});
	}
};
