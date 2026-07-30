<?php

namespace App\Providers;

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
