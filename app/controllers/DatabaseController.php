<?php

class DatabaseController extends BaseController
{
	public function show ()
	{
		$user = Auth::user ();
		$creds = DatabaseCredentials::forUser ($user);
		
		return View::make ('database.show')->with ('dbUsername', $creds[0])->with ('dbPassword', $creds[1]);
	}
}