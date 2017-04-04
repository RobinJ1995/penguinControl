<?php

namespace App\Http\Controllers;

use App\DatabaseCredentials;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DatabaseController extends Controller
{
	public function show ()
	{
		$user = Auth::user ();
		$creds = DatabaseCredentials::forUser ($user);
		$phpmyadminUrl = Config::get ('penguin.phpmyadmin_url');
		
		return view ('database.show')->with ('dbUsername', $creds[0])->with ('dbPassword', $creds[1])->with ('phpmyadminUrl', $phpmyadminUrl);
	}
}