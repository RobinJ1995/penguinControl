<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class StaffPageController extends Controller
{
	public function index ()
	{
		$pages = Page::all ();
		
		return view ('staff.page.index', compact ('pages'));
	}
	
	public function create ()
	{
		return view ('staff.page.create');
	}

	public function store ()
	{
		$name = snake_case (preg_replace ('/[^A-Za-z0-9\-\_\ ]/', '', strtolower (Input::get ('title'))));
		
		$validator = Validator::make
		(
			array
			(
				'Titel' => Input::get ('title'),
				'Naam' => $name,
				'Status' => Input::get ('published'),
				'Gewicht' => Input::get ('weight'),
				'Inhoud' => Input::get ('content')
			),
			array
			(
				'Titel' => array ('required', 'max:64', 'unique:page,title'),
				'Naam' => array ('required', 'max:64', 'unique:page,name', 'alpha_dash'),
				'Status' => array ('required', 'integer', 'in:-1,0,1'),
				'Gewicht' => array ('required', 'integer', 'min:-127', 'max:127'),
				'Inhoud' => 'required'
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/page/create')->withInput ()->withErrors ($validator);
		
		$page = new Page ();
		$page->title = Input::get ('title');
		$page->name = $name;
		$page->published = Input::get ('published');
		$page->weight = Input::get ('weight');
		$page->content = Input::get ('content');
		
		$page->save ();
		
		Log::log ('Pagina aangemaakt', NULL, $page);
		
		return Redirect::to ('/staff/page')->with ('alerts', array (new Alert ('Pagina toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($page)
	{
		return view ('staff.page.edit', compact ('page'));
	}
	
	public function update ($page)
	{
		$validator = Validator::make
		(
			array
			(
				'Status' => Input::get ('published'),
				'Gewicht' => Input::get ('weight'),
				'Inhoud' => Input::get ('content')
			),
			array
			(
				'Status' => array ('required', 'integer', 'in:-1,0,1'),
				'Gewicht' => array ('required', 'integer', 'min:-127', 'max:127'),
				'Inhoud' => 'required'
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/page/'. $page->id . '/edit')->withInput ()->withErrors ($validator);
		
		$page->published = Input::get ('published');
		$page->weight = Input::get ('weight');
		$page->content = Input::get ('content');
		
		$page->save ();
		
		Log::log ('Pagina bijgewerkt', NULL, $page);
		
		return Redirect::to ('/staff/page')->with ('alerts', array (new Alert ('Pagina bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($page)
	{
		$page->delete ();
		
		Log::log ('Pagina verwijderd', NULL, $page);
		
		return Redirect::to ('/staff/page')->with ('alerts', array (new Alert ('Pagina verwijderd', Alert::TYPE_SUCCESS)));
	}
}
