<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
	public function show ()
	{
		if (Config::get ('penguin.website', false))
			return Redirect::to ('/page/home');
		
		return Redirect::to ('/user/login');
	}
}