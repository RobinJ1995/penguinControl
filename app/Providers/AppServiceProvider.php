<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot ()
	{
		// This app rolls its own login flow rather than using Laravel's scaffolding //
		Authenticate::redirectUsing (fn () => '/user/login');

		// The views style pagination for Foundation 5, whose markup the Bootstrap 4
		// presenter matches far more closely than the Tailwind default does //
		Paginator::useBootstrapFour ();
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register ()
	{
	}
}
