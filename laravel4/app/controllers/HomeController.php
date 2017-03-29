<?php

class HomeController extends BaseController
{
	public function show ()
	{
		return Redirect::to ('/page/home');
	}
}