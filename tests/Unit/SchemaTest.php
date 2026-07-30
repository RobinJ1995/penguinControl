<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The baseline schema is shared with other server components -- libnss-mysql
 * serves passwd, group and shadow from it, and Postfix reads the mail tables --
 * so these column names are a contract, not an implementation detail.
 */
class SchemaTest extends TestCase
{
	use RefreshDatabase;

	public function test_the_baseline_migration_creates_every_table_the_models_use (): void
	{
		$expected = [
			'user', 'user_info', 'user_group', 'user_limit', 'user_log', 'group',
			'log', 'page', 'system_task', 'vhost', 'ftp',
			'mail_domain', 'mail_user', 'mail_forward',
		];

		foreach ($expected as $table)
			$this->assertTrue (Schema::hasTable ($table), "the $table table is missing");
	}

	public function test_the_columns_other_server_components_read_are_present (): void
	{
		// docs/setup/nss.md
		$this->assertTrue (Schema::hasColumns ('user', [
			'uid', 'gid', 'user_info_id', 'crypt', 'gcos', 'homedir', 'shell',
			'lastchange', 'min', 'max', 'warn', 'inact', 'expire', 'flag',
		]));
		$this->assertTrue (Schema::hasColumns ('user_info', [
			'username', 'fname', 'lname', 'email', 'validated', 'lastchange',
		]));
		$this->assertTrue (Schema::hasColumns ('group', ['name', 'gid']));
		$this->assertTrue (Schema::hasColumns ('user_group', ['uid', 'gid']));

		// docs/setup/mail.md
		$this->assertTrue (Schema::hasColumn ('user', 'mail_enabled'));
		$this->assertTrue (Schema::hasColumns ('mail_domain', ['uid', 'domain']));
		$this->assertTrue (Schema::hasColumns ('mail_user', ['uid', 'email', 'mail_domain_id', 'password']));
		$this->assertTrue (Schema::hasColumns ('mail_forward', ['uid', 'source', 'mail_domain_id', 'destination']));
	}

	public function test_the_ftp_rename_migration_has_been_applied (): void
	{
		$this->assertTrue (Schema::hasColumn ('ftp', 'username'),
			'2017_05_31_091626_ChangeFtpUserFieldName should have renamed ftp.user');
		$this->assertFalse (Schema::hasColumn ('ftp', 'user'));
		$this->assertTrue (Schema::hasColumn ('ftp', 'passwd'));
	}

	public function test_the_billing_log_uses_the_english_column_names (): void
	{
		$this->assertTrue (Schema::hasColumns ('user_log', ['user_info_id', 'time', 'new', 'status']));
	}
}
