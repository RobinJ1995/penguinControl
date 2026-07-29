<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
	private ?Collection $pages = null;

	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function boot ()
	{
		View::composer
		(
			'*',
			function ($view)
			{
				$view->with ('siteMenu', $this->pages ());
			}
		);
	}

	/**
	 * The published pages, fetched once per request and only when a view is
	 * actually rendered. Querying in boot () would mean no console command --
	 * `migrate` included -- could run before the schema existed.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	private function pages ()
	{
		if ($this->pages === null)
		{
			$this->pages = Page::where ('published', '1')
				->orderBy ('weight')
				->get ();
		}

		return $this->pages;
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register ()
	{
	}
}
