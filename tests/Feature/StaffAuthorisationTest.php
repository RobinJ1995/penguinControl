<?php

namespace Tests\Feature;

use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The staff area used to be gated on `auth` alone. is_admin () decided which menu
 * entries to draw, and nothing decided who could reach the URLs behind them, so an
 * ordinary user who typed one got in -- including staff/user/user/{user}/login, which
 * logs you in as the user you name.
 *
 * RoutingTest covers the anonymous case. These cover the logged-in-but-not-staff case,
 * which is the one that was missing.
 */
class StaffAuthorisationTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * One per controller under Http/Controllers/Staff, plus the two escalation paths
	 * that matter most: impersonation and phpinfo //
	 */
	private const STAFF_URIS = [
		'/staff/user/user',
		'/staff/user/user/create',
		'/staff/user/limit',
		'/staff/user/group',
		'/staff/user/log',
		'/staff/website/vhost',
		'/staff/ftp',
		'/staff/mail/domain',
		'/staff/page',
		'/staff/system/phpinfo',
		'/staff/system/log',
		'/staff/system/systemtask',
		// There is no bare /staff/maintenance; the destructive endpoints hang off it
		// directly. The middleware refuses before the controller runs, which is the
		// point -- these are the ones that regenerate every vHost on the box //
		'/staff/maintenance/vhost/generate',
		'/staff/maintenance/system/check',
		'/problem-solver/all/dry',
	];

	protected function setUp (): void
	{
		parent::setUp ();

		$this->seed ();
	}

	private function user ($username)
	{
		return UserInfo::where ('username', $username)->firstOrFail ()->user;
	}

	public function test_an_ordinary_user_is_refused_the_staff_area (): void
	{
		$penguin = $this->user ('penguin');

		$this->assertFalse ($penguin->isAdmin (),
			'the fixture this test depends on is meant to be a non-administrator');

		foreach (self::STAFF_URIS as $uri)
			$this->actingAs ($penguin)->get ($uri)->assertForbidden ();
	}

	public function test_an_ordinary_user_cannot_impersonate_somebody_else (): void
	{
		$penguin = $this->user ('penguin');
		$admin = $this->user ('admin');

		$this->actingAs ($penguin)
			->get ('/staff/user/user/' . $admin->id . '/login')
			->assertForbidden ();
	}

	public function test_an_ordinary_user_cannot_queue_the_problem_solver_against_somebody_else (): void
	{
		$penguin = $this->user ('penguin');
		$admin = $this->user ('admin');

		// The problem solver runs as root and chowns what it creates //
		$this->actingAs ($penguin)
			->get ('/problem-solver/' . $admin->id)
			->assertForbidden ();

		$this->actingAs ($penguin)
			->get ('/problem-solver/schedule?userId=' . $admin->id)
			->assertForbidden ();
	}

	public function test_a_user_may_still_run_the_problem_solver_against_themselves (): void
	{
		$penguin = $this->user ('penguin');

		$this->actingAs ($penguin)->get ('/problem-solver')->assertOk ();
		$this->actingAs ($penguin)
			->get ('/problem-solver/' . $penguin->id)
			->assertOk ();
	}

	public function test_an_administrator_still_reaches_the_staff_area (): void
	{
		$admin = $this->user ('admin');

		$this->assertTrue ($admin->isAdmin ());

		$this->actingAs ($admin)->get ('/staff/user/user')->assertOk ();
	}

	/**
	 * The shell dropdowns and the login-link fields read from configuration and helpers
	 * now rather than from literals repeated per view, so a mistake in one of them is a
	 * Blade error at render time and nothing catches it until somebody opens the page //
	 */
	public function test_the_pages_that_offer_a_shell_render (): void
	{
		$admin = $this->user ('admin');
		$penguin = $this->user ('penguin');

		$this->actingAs ($admin)->get ('/staff/user/user/create')->assertOk ();
		$this->actingAs ($admin)->get ('/staff/user/user/' . $penguin->id . '/edit')->assertOk ();
		$this->actingAs ($admin)->get ('/staff/user/user/' . $penguin->id . '/more')->assertOk ();
		$this->actingAs ($penguin)->get ('/user/edit')->assertOk ();
	}

	public function test_every_offered_shell_passes_the_shell_validation_rule (): void
	{
		$shells = allowed_shells ();

		$this->assertNotEmpty ($shells);

		// The three lists this replaced disagreed, so "Fish" offered by the staff create
		// screen was rejected by that same screen's validator //
		foreach (array_keys ($shells) as $shell)
			$this->assertStringContainsString ($shell, allowed_shells_rule ());
	}
}
