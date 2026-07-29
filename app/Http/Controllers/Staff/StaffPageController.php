<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

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
		$name = Str::snake (preg_replace ('/[^A-Za-z0-9\-\_\ ]/', '', strtolower (request ('title'))));
		
		$validator = Validator::make
		(
			array
			(
				'Titel' => request ('title'),
				'Naam' => $name,
				'Status' => request ('published'),
				'Gewicht' => request ('weight'),
				'Inhoud' => request ('content')
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
		$page->title = request ('title');
		$page->name = $name;
		$page->published = request ('published');
		$page->weight = request ('weight');
		$page->content = request ('content');
		
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
				'Status' => request ('published'),
				'Gewicht' => request ('weight'),
				'Inhoud' => request ('content')
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
		
		$page->published = request ('published');
		$page->weight = request ('weight');
		$page->content = request ('content');
		
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
