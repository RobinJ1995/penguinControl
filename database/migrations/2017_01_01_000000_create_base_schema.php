<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The baseline schema.
 *
 * penguinControl predates its own migrations: the two 2017 migrations that
 * follow this one only *alter* tables that were expected to already exist,
 * having been created out of band from a SQL dump. That made `migrate` useless
 * on a fresh database.
 *
 * This migration reproduces that schema so the application can be installed
 * from the repository alone. It is dated ahead of the existing migrations so
 * they still apply on top of it -- in particular the ftp.user -> ftp.username
 * rename, which is why the ftp table is created here with a "user" column.
 *
 * Several tables are shared with other server components rather than being
 * private to this application, so the column names are load-bearing:
 * libnss-mysql reads user/user_info/group/user_group to serve passwd, group
 * and shadow (docs/setup/nss.md), and Postfix reads mail_domain, mail_user and
 * mail_forward (docs/setup/mail.md). The foreign keys' ON DELETE CASCADE
 * behaviour is relied upon when a user is removed.
 */
return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		Schema::create ('user_info',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->string ('username', 50)->unique ()->comment ('Username');
				$table->string ('fname', 255)->comment ('First name');
				$table->string ('lname', 255)->comment ('Surname');
				$table->string ('email', 45)->comment ('E-mail address');
				$table->string ('schoolnr', 50)->comment ('Student or staff number');
				$table->timestamp ('lastchange')->useCurrent ()->useCurrentOnUpdate ();
				$table->boolean ('validated')->default (0);
				$table->string ('validationcode', 64)->nullable ();
				$table->string ('logintoken', 64)->nullable ();
				$table->text ('etc')->nullable ();
			}
		);

		Schema::create ('group',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->string ('name', 30)->default ('');
				$table->string ('password', 64)->default ('x');
				$table->integer ('gid')->unique ();
			}
		);

		Schema::create ('user',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid')->unique ()->comment ('Global user id');
				$table->integer ('user_info_id');
				$table->string ('crypt', 255)->default ('x')->comment ('Hashed password');
				$table->string ('gcos', 255)->comment ('gcos field');
				$table->integer ('gid')->default (100)->comment ('Primary group id');
				$table->string ('homedir', 255)->default ('')->comment ("The user's home directory");
				$table->string ('shell', 20)->default ('/bin/bash')->comment ("The user's login shell");
				$table->bigInteger ('lastchange')->default (1);
				$table->bigInteger ('min')->default (0);
				$table->bigInteger ('max')->default (99999);
				$table->bigInteger ('warn')->default (0);
				$table->bigInteger ('inact')->default (0);
				$table->bigInteger ('expire')->nullable ();
				$table->unsignedBigInteger ('flag')->default (0);
				$table->string ('smb_lm', 255);
				$table->string ('smb_nt', 255);
				$table->bigInteger ('diskusage')->default (0)->comment ('Filled in by the calculate_disk_usage system task');
				$table->boolean ('svn_enabled')->default (0)->comment ('enable=1, disable=0');
				$table->boolean ('mail_enabled')->default (0)->comment ('enable=1, disable=0, blocked=-1');
				$table->string ('remember_token', 100)->nullable ()->default ('');

				$table->index ('gid');
				$table->index ('user_info_id');
				$table->foreign ('user_info_id')->references ('id')->on ('user_info');
			}
		);

		Schema::create ('user_group',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid')->default (0);
				$table->integer ('gid')->default (0);

				$table->unique (['uid', 'gid']);
				$table->index ('uid');
				$table->index ('gid');
				$table->foreign ('gid')->references ('gid')->on ('group');
				$table->foreign ('uid')->references ('uid')->on ('user');
			}
		);

		Schema::create ('user_limit',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				// The row whose uid is NULL holds the global defaults //
				$table->integer ('uid')->nullable ()->unique ();
				$table->integer ('ftp')->default (3);
				$table->integer ('mysql_user_max')->default (5);
				$table->integer ('mysql_db_max')->default (5);
				$table->integer ('vhost')->default (3);
				$table->integer ('mail_domain')->default (1);
				$table->integer ('mail_user')->default (3);
				$table->integer ('mail_forward')->default (3);
				$table->integer ('diskusage')->default (25000);

				$table->foreign ('uid')->references ('uid')->on ('user');
			}
		);

		Schema::create ('user_log',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('user_info_id');
				$table->timestamp ('time')->useCurrent ();
				$table->boolean ('new')->default (1);
				$table->tinyInteger ('status')->default (0)
					->comment ('-1 = Not to be billed // 0 = To be billed // 1 = Billed');

				$table->index ('user_info_id');
				$table->foreign ('user_info_id')->references ('id')->on ('user_info');
			}
		);

		Schema::create ('log',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('user_id')->nullable ();
				$table->string ('message', 255);
				$table->text ('data')->nullable ();
				$table->timestamps ();

				$table->index ('user_id');
				$table->foreign ('user_id')->references ('id')->on ('user')->onDelete ('cascade');
			}
		);

		Schema::create ('page',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->string ('name', 64)->unique ();
				$table->string ('title', 45);
				$table->text ('content')->nullable ();
				$table->timestamps ();
				$table->softDeletes ();
				$table->boolean ('published')->default (0)
					->comment ('-1 = Not published -- 0 = Not in the menu -- 1 = In the menu');
				$table->tinyInteger ('weight')->default (0);
			}
		);

		Schema::create ('system_task',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->string ('type', 64)->nullable ();
				$table->text ('data')->nullable ()->comment ('JSON object');
				$table->integer ('start')->nullable ()->comment ('Unix timestamp');
				$table->integer ('end')->nullable ()->comment ('Unix timestamp');
				$table->integer ('interval')->nullable ()->comment ('Number of seconds');
				$table->smallInteger ('exitcode')->nullable ()->comment ('Last exit code');
				$table->boolean ('started')->default (0);
				$table->integer ('lastRun')->nullable ();
			}
		);

		Schema::create ('vhost',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid');
				$table->string ('docroot', 256);
				$table->string ('basedir', 255)->nullable ();
				$table->string ('servername', 256);
				$table->tinyText ('serveralias');
				$table->string ('serveradmin', 64);
				$table->text ('custom')->comment ('Raw configuration appended to the generated vHost');
				$table->boolean ('cgi')->default (1);
				$table->boolean ('ssl')->default (0)->comment ('0: http, 1: https, 2: https with redirect');
				$table->boolean ('locked')->default (0);

				$table->index ('uid');
				$table->foreign ('uid')->references ('uid')->on ('user')->onDelete ('cascade');
			}
		);

		Schema::create ('ftp',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid');
				// Renamed to "username" by 2017_05_31_091626_ChangeFtpUserFieldName //
				$table->string ('user', 50)->unique ();
				$table->string ('passwd', 128);
				$table->tinyText ('dir');
				$table->boolean ('locked')->default (0);

				$table->index ('uid');
				$table->foreign ('uid')->references ('uid')->on ('user')->onDelete ('cascade');
			}
		);

		Schema::create ('mail_domain',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid');
				$table->string ('domain', 50)->unique ();

				$table->index ('uid');
				$table->foreign ('uid')->references ('uid')->on ('user')->onDelete ('cascade');
			}
		);

		Schema::create ('mail_user',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid');
				$table->string ('email', 80);
				$table->integer ('mail_domain_id')->nullable ();
				$table->string ('password', 128);

				$table->index ('uid');
				$table->index ('email');
				$table->index ('mail_domain_id');
				$table->foreign ('uid')->references ('uid')->on ('user')->onDelete ('cascade');
				$table->foreign ('mail_domain_id')->references ('id')->on ('mail_domain');
			}
		);

		Schema::create ('mail_forward',
			function (Blueprint $table)
			{
				$table->integer ('id', true);
				$table->integer ('uid');
				$table->string ('source', 80)->nullable ();
				$table->integer ('mail_domain_id')->nullable ();
				$table->text ('destination');

				$table->index ('uid');
				$table->index ('source');
				$table->index ('mail_domain_id');
				$table->foreign ('uid')->references ('uid')->on ('user')->onDelete ('cascade');
				$table->foreign ('mail_domain_id')->references ('id')->on ('mail_domain');
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
		Schema::dropIfExists ('mail_forward');
		Schema::dropIfExists ('mail_user');
		Schema::dropIfExists ('mail_domain');
		Schema::dropIfExists ('ftp');
		Schema::dropIfExists ('vhost');
		Schema::dropIfExists ('system_task');
		Schema::dropIfExists ('page');
		Schema::dropIfExists ('log');
		Schema::dropIfExists ('user_log');
		Schema::dropIfExists ('user_limit');
		Schema::dropIfExists ('user_group');
		Schema::dropIfExists ('user');
		Schema::dropIfExists ('group');
		Schema::dropIfExists ('user_info');
	}
};
