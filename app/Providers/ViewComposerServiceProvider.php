<?php

namespace App\Providers;

use App\Menu;
use App\Models\Page;
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
		$pages = Page::where ('published', '1')
			->orderBy ('weight')
			->get ();
		
		View::composer
		(
			'*',
			function ($view) use ($pages)
			{
				$view->with ('siteMenu', $pages);
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