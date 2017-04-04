<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
	public function show ()
	{
		return Redirect::to ('/page/home');
	}
}