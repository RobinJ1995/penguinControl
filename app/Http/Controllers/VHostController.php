<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use App\Models\SystemTask;
use App\Models\Vhost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class VHostController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		$vhosts = Vhost::where ('uid', $user->uid)->get ();
		
		$apacheReloadInterval = SystemTask::where ('type', SystemTask::TYPE_APACHE_RELOAD)
			->where
			(
				function ($query)
				{
					$query->where ('end', '>', time ())
						->orWhereNull ('end');
				}
			)->min ('interval');
		$apacheReloadInterval = SystemTask::friendlyInterval ($apacheReloadInterval);
		
		return view ('website.vhost.index', compact ('user', 'userInfo', 'vhosts', 'apacheReloadInterval'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! Vhost::allowNew ($user))
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You are only allowed to create ' . Vhost::getLimit ($user) . ' vHosts.', Alert::TYPE_ALERT)));
		
		return view ('website.vhost.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! Vhost::allowNew ($user))
			return Redirect::to ('/website/vhost/create')->withInput ()->with ('alerts', array (new Alert ('You are only allowed to create ' . Vhost::getLimit ($user) . ' vHosts.', Alert::TYPE_ALERT)));
		
		$servername = @strtolower (Input::get ('servername'));
		$docroot = @trailing_slash (Input::get ('docroot'));
		
		$validator = Validator::make
		(
			array
			(
				'Host' => $servername,
				//'Beheerder' => Input::get ('serveradmin'),
				//'Alias' => Input::get ('serveralias'),
				'Document root' => $docroot,
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Host' => array ('required', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				//'Beheerder' => array ('required', 'email'),
				//'Alias' => array ('different:Host', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/', 'vhost_subdomain:' . $user->userInfo->username),
				'Document root' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$vhost = new Vhost ();
		$vhost->uid = $user->uid;
		$vhost->docroot = $user->homedir . '/' . $docroot;
		$vhost->servername = $servername;
		$vhost->serveralias = 'www.' . $servername;
		$vhost->serveradmin = $user->userInfo->username . '@' . $servername;
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost created', $user->id, $vhost);
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_APACHE_RELOAD;
		$task->save ();
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$insideHomedir = substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir;
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost')->withInput ()->with ('alerts', array (new Alert ('You are not allowed to edit this vHost.', Alert::TYPE_ALERT)));
		
		return view ('website.vhost.edit', compact ('user', 'vhost', 'insideHomedir'));
	}
	
	public function update ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$insideHomedir = (Input::get ('outsideHomedir') !== 'true');
		$docroot = @trailing_slash (Input::get ('docroot'));
		
		$validator = Validator::make
		(
			array
			(
				'Document root' => $docroot,
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Document root' => array ($insideHomedir ? 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/' : 'optional'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('You are not allowed to edit this vHost.', Alert::TYPE_ALERT)));
		
		if ($insideHomedir)
			$vhost->docroot = $user->homedir . '/' . $docroot;
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost modified', NULL, $vhost);
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_APACHE_RELOAD;
		$task->save ();
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('You don\'t own this vHost!', Alert::TYPE_ALERT)));
		
		$vhost->delete ();
		
		Log::log ('vHost removed', NULL, $vhost);
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_APACHE_RELOAD;
		$task->save ();
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost removed', Alert::TYPE_SUCCESS)));
	}

}
