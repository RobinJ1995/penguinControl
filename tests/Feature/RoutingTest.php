<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests for how the application routes an anonymous visitor.
 *
 * The end-to-end suite under tests/e2e covers behaviour against a real Apache
 * and MariaDB; these run against SQLite in memory and exist to catch a broken
 * bootstrap or route table quickly.
 */
class RoutingTest extends TestCase
{
	use RefreshDatabase;

	public function test_the_site_root_redirects_an_anonymous_visitor_to_the_login_form (): void
	{
		// HomeController@show always redirects; it never returns a page
		$this->get ('/')->assertRedirect ('/user/login');
	}

	public function test_the_login_form_renders (): void
	{
		$this->get ('/user/login')
			->assertOk ()
			->assertSee ('Username');
	}

	public function test_the_panel_is_closed_to_anonymous_visitors (): void
	{
		foreach (['/user/start', '/website/vhost', '/ftp', '/mail', '/staff/user/user'] as $uri)
			$this->get ($uri)->assertRedirect ('/user/login');
	}

	public function test_every_route_resolves_to_a_callable_action (): void
	{
		$unresolved = [];

		foreach (app ('router')->getRoutes () as $route)
		{
			$action = $route->getAction ('uses');

			if (! is_string ($action) || ! str_contains ($action, '@'))
				continue;

			[$class, $method] = explode ('@', $action, 2);

			if (! class_exists ($class) || ! method_exists ($class, $method))
				$unresolved[] = $route->uri () . ' -> ' . $action;
		}

		$this->assertSame ([], $unresolved,
			'these routes point at a controller or method that does not exist');
	}
}
