<?php

namespace App\Providers;

use App\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function boot()
	{
		View::composer ('part.controlMenu',
			function ($view)
			{
				$view->with ('controlMenu', Menu::getControl ());
			}
		);
		View::composer
		(
			'*',
			function ($view)
			{
				$view->with ('siteMenu', Menu::getSite ());
			}
		);
		View::composer
		(
			'*',
			function ($view)
			{
				$view->with ('staffMenu', Menu::getStaff ());
			}
		);
	}
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}
}