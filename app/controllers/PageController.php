<?php

class PageController extends BaseController
{
	public function show ($page)
	{
		return View::make ('page', compact ('page'));
	}
}