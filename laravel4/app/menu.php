<?php

// View Composer // http://laravel.com/docs/responses#view-composers //
View::composer ('controlMenu',
	function ($view)
	{
		$view->with ('controlMenu', Menu::getControl ());
	}
);
View::composer ('siteMenu',
	function ($view)
	{
		$view->with ('siteMenu', Menu::getSite ());
	}
);
View::composer ('staffMenu',
	function ($view)
	{
		$view->with ('staffMenu', Menu::getStaff ());
	}
);
