<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Vhost;
use App\Provisioning\ProvisioningException;
use App\Provisioning\VhostRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * What the panel hands Apache.
 *
 * Until this existed the generated configuration could only be inspected by letting
 * Vhost::save () write it into /etc/apache2, so the end-to-end suite was the only thing
 * that ever looked at it. These assert the output directly, and in particular that values
 * which would break out of a directive are refused rather than interpolated.
 */
class VhostRendererTest extends TestCase
{
	use RefreshDatabase;

	private function makeUser ($username = 'penguin', $homedir = NULL, $expire = -1)
	{
		$group = Group::where ('gid', 2000)->first ();

		if ($group === NULL)
		{
			// The models guard mass assignment, so build this the way the seeder does //
			$group = new Group ();
			$group->name = 'user';
			$group->password = 'x';
			$group->gid = 2000;
			$group->save ();
		}

		$userInfo = new UserInfo ();
		$userInfo->username = $username;
		$userInfo->fname = 'Test';
		$userInfo->lname = 'User';
		$userInfo->email = $username . '@localhost';
		$userInfo->validated = 1;
		$userInfo->save ();

		$user = new User ();
		$user->uid = 6000 + UserInfo::count ();
		$user->user_info_id = $userInfo->id;
		$user->gcos = 'Test User';
		$user->gid = $group->gid;
		$user->homedir = $homedir ?? '/home/' . $username;
		$user->shell = '/bin/bash';
		$user->diskusage = 0;
		$user->mail_enabled = 1;
		$user->expire = $expire;
		$user->setPassword ('irrelevant');
		$user->save ();

		return $user;
	}

	private function makeVhost (User $user, array $overrides = [])
	{
		$vhost = new Vhost ();
		$vhost->uid = $user->uid;
		$vhost->docroot = $user->homedir . '/public_html';
		$vhost->servername = 'site.example.test';
		$vhost->serveralias = 'www.site.example.test';
		$vhost->serveradmin = 'admin@example.test';
		$vhost->cgi = 0;
		$vhost->ssl = 0;
		$vhost->locked = 0;

		foreach ($overrides as $key => $value)
			$vhost->{$key} = $value;

		return $vhost;
	}

	private function render (Vhost $vhost)
	{
		return (new VhostRenderer ())->render ($vhost);
	}

	public function test_it_renders_the_directives_apache_is_expected_to_load (): void
	{
		$user = $this->makeUser ();
		$output = $this->render ($this->makeVhost ($user));

		$this->assertStringContainsString ('<VirtualHost *:80>', $output);
		$this->assertStringContainsString ('ServerName site.example.test', $output);
		$this->assertStringContainsString ('ServerAlias www.site.example.test', $output);
		$this->assertStringContainsString ('AssignUserID penguin user', $output);
		$this->assertStringContainsString ('DocumentRoot "/home/penguin/public_html"', $output);
		$this->assertStringContainsString ('Require all granted', $output);
		$this->assertStringContainsString ('</VirtualHost>', $output);
	}

	/**
	 * Certbot appends to the last line when the file does not end in a newline //
	 */
	/**
	 * The end-to-end suite asserts these exact directive lines against a real Apache.
	 * It cannot run here, so this pins the same strings where they can be checked //
	 */
	public function test_it_matches_what_the_end_to_end_suite_asserts (): void
	{
		$user = $this->makeUser ();
		$vhost = $this->makeVhost ($user, [
			'servername' => 'penguin.example.test',
			'serveralias' => 'www.penguin.example.test',
			'docroot' => '/home/penguin/public_html/'
		]);

		$output = $this->render ($vhost);

		foreach ([
			'<VirtualHost *:80>',
			'ServerName penguin.example.test',
			'AssignUserID penguin user',
			'DocumentRoot "/home/penguin/public_html/"',
			'AllowOverride All',
			'Require all granted',
			'php_admin_value open_basedir "/home/penguin/public_html/:/home/penguin:/tmp:/usr/share/php"',
			'CustomLog "/var/log/apache2/vhost/VHOST_penguin_penguin.example.test.log" combined'
		] as $expected)
			$this->assertStringContainsString ($expected, $output,
				'tests/e2e/features/vhost.feature asserts this line');

		$this->assertStringNotContainsString ('/usr/share/php/:', $output);
	}

	public function test_it_ends_with_a_newline (): void
	{
		$this->assertStringEndsWith (PHP_EOL, $this->render ($this->makeVhost ($this->makeUser ())));
	}

	public function test_open_basedir_confines_the_site_without_a_stray_separator (): void
	{
		$user = $this->makeUser ();
		$output = $this->render ($this->makeVhost ($user));

		$this->assertStringContainsString (
			'php_admin_value open_basedir "/home/penguin/public_html:/home/penguin:/tmp:/usr/share/php"',
			$output);
		$this->assertStringNotContainsString ('/usr/share/php/:', $output);
		$this->assertStringNotContainsString ('::', $output);
	}

	public function test_an_extra_base_directory_is_appended (): void
	{
		$user = $this->makeUser ();
		$output = $this->render ($this->makeVhost ($user, ['basedir' => '/srv/shared']));

		$this->assertStringContainsString (':/usr/share/php:/srv/shared"', $output);
	}

	public function test_cgi_adds_both_the_handler_and_the_option (): void
	{
		$user = $this->makeUser ();

		$with = $this->render ($this->makeVhost ($user, ['cgi' => 1]));
		$this->assertStringContainsString ('AddHandler cgi-script .cgi', $with);
		$this->assertStringContainsString ('Options +ExecCGI +FollowSymLinks', $with);

		$without = $this->render ($this->makeVhost ($user, ['cgi' => 0]));
		$this->assertStringNotContainsString ('cgi-script', $without);
		$this->assertStringContainsString ('Options +FollowSymLinks', $without);
	}

	public function test_an_expired_user_gets_the_placeholder_document_root (): void
	{
		$expired = $this->makeUser ('gone', '/home/gone', 1);
		$output = $this->render ($this->makeVhost ($expired));

		$this->assertStringNotContainsString ('/home/gone/public_html', $output);
		$this->assertStringContainsString (VhostRenderer::expiredDocroot (), $output);
	}

	public function test_the_placeholder_document_root_actually_exists (): void
	{
		// The constant this replaces pointed at a directory that never shipped //
		$this->assertDirectoryExists (VhostRenderer::expiredDocroot ());
	}

	public function test_paths_come_from_configuration (): void
	{
		Config::set ('penguin.paths.vhost_log', '/var/log/httpd/vhost');

		$output = $this->render ($this->makeVhost ($this->makeUser ()));

		$this->assertStringContainsString ('CustomLog "/var/log/httpd/vhost/VHOST_penguin_site.example.test.log"', $output);
	}

	public function test_allow_override_is_configurable_and_defaults_to_all (): void
	{
		$user = $this->makeUser ();

		$this->assertStringContainsString ('AllowOverride All', $this->render ($this->makeVhost ($user)));

		Config::set ('penguin.apache.allow_override', 'FileInfo Indexes Limit AuthConfig');
		$this->assertStringContainsString ('AllowOverride FileInfo Indexes Limit AuthConfig',
			$this->render ($this->makeVhost ($user)));
	}

	/**
	 * Apache has no quoting mechanism for directive arguments, so a newline in a value is
	 * a new directive and a quote in a path ends the argument. The old code interpolated
	 * all of these with str_replace () and no checks //
	 */
	public static function injectionProvider (): array
	{
		return [
			'newline in ServerName' => ['servername', "site.test\n\tRequire all denied"],
			'space in ServerName' => ['servername', 'site.test Alias evil.test'],
			'quote in document root' => ['docroot', '/home/penguin/"/../../etc'],
			'newline in document root' => ['docroot', "/home/penguin\n\tRequire all granted"],
			'relative document root' => ['docroot', 'public_html'],
			'newline in ServerAdmin' => ['serveradmin', "a@b.test\n\tServerName evil.test"],
			'newline in extra base directory' => ['basedir', "/srv\n\tRequire all granted"],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('injectionProvider')]
	public function test_it_refuses_values_that_would_break_out_of_a_directive ($field, $value): void
	{
		$user = $this->makeUser ();

		$this->expectException (ProvisioningException::class);

		$this->render ($this->makeVhost ($user, [$field => $value]));
	}

	/**
	 * ServerAlias is a space-separated list, so a newline in the column is flattened onto
	 * the one directive rather than starting another, and every piece still has to look
	 * like a hostname. A value that survives that is junk aliases, not a directive //
	 */
	public function test_a_newline_in_the_alias_column_cannot_start_a_new_directive (): void
	{
		$user = $this->makeUser ();
		$output = $this->render ($this->makeVhost ($user,
			['serveralias' => "www.site.test\n\tServerName evil.test"]));

		$directives = [];
		foreach (explode (PHP_EOL, $output) as $line)
			$directives[] = strtok (trim ($line), ' ');

		$this->assertSame (1, count (array_keys ($directives, 'ServerName')),
			'the alias column started a second ServerName directive');
		$this->assertStringContainsString ('ServerAlias www.site.test ServerName evil.test', $output);
	}

	public function test_a_path_in_the_alias_column_is_refused (): void
	{
		$user = $this->makeUser ();

		$this->expectException (ProvisioningException::class);
		$this->render ($this->makeVhost ($user, ['serveralias' => "www.site.test\n\tCustomLog /dev/null"]));
	}

	public function test_a_wildcard_server_name_is_still_accepted (): void
	{
		$user = $this->makeUser ();

		$this->assertStringContainsString ('ServerName *.example.test',
			$this->render ($this->makeVhost ($user, ['servername' => '*.example.test'])));
	}

	public function test_multiple_aliases_are_normalised_onto_one_directive (): void
	{
		$user = $this->makeUser ();
		$output = $this->render ($this->makeVhost ($user,
			['serveralias' => "  www.site.example.test   alias.example.test\t"]));

		$this->assertStringContainsString ('ServerAlias www.site.example.test alias.example.test', $output);
	}

	public function test_an_empty_alias_omits_the_directive_rather_than_emitting_a_bare_one (): void
	{
		$user = $this->makeUser ();

		$this->assertStringNotContainsString ('ServerAlias',
			$this->render ($this->makeVhost ($user, ['serveralias' => ''])));
	}
}
