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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffVHostController extends Controller
{
	public function index ()
	{
		$vhosts = Vhost::paginate ();
		
		$searchUrl = action ('Staff\StaffVHostController@search');
		
		return view ('staff.website.vhost.index', compact ('vhosts', 'searchUrl'));
	}
	
	public function search ()
	{
		$host = Input::get ('host');
		$docroot = Input::get ('docroot');
		$basedir = Input::get ('basedir');
		$username = Input::get ('username');
		
		$query = Vhost::where
		(
			function ($query) use ($host)
			{
				$query->where ('servername', 'LIKE', '%' . $host . '%')
					->orWhere ('serveralias', 'LIKE', '%' . $host . '%');
			}
		)->where
		(
			function ($query) use ($docroot)
			{
				$query->where ('docroot', 'LIKE', '%'  . $docroot . '%');
				if (empty ($docroot))
					$query->orWhereNull ('docroot');
			}
		)->where
		(
			function ($query) use ($basedir)
			{
				$query->where ('basedir', 'LIKE', '%' . $basedir . '%');
				if (empty ($basedir))
					$query->orWhereNull ('basedir');
			}
		);
		
		if (! empty ($username))
		{
			$uid = '';
			
			$userInfos = UserInfo::where ('username', 'LIKE', '%' . $username . '%')->get ();
			$uids = array ();
			
			foreach ($userInfos as $userInfo)
			{
				$user = $userInfo->user;
				$uid = $user->uid;
				
				$uids[] = $uid;
			}
			
			$query = $query->whereIn ('uid', $uids);
		}
		
		$count = $query->count ();
		$vhosts = $query->paginate ();
		
		$searchUrl = action ('Staff\StaffVHostController@search');
		
		return view ('staff.website.vhost.search', compact ('count', 'vhosts', 'searchUrl'));
	}
	
	public function create ()
	{
		$users = array ();
		$user = Auth::user ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return view ('staff.website.vhost.create', compact ('users', 'user'));
	}

	public function store ()
	{
		$ownerUser = User::where ('uid', Input::get ('uid'))->firstOrFail ();
		
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Host' => Input::get ('servername'),
				'Beheerder' => Input::get ('serveradmin'),
				'Alias' => Input::get ('serveralias'),
				'Document root' => Input::get ('docroot'),
				'Basedir' => Input::get ('basedir'),
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Host' => array ('required', 'unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Beheerder' => array ('required', 'email'),
				'Alias' => array ('different:Host', 'unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+(\s[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+)*$/'), //'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Document root' => array ('required', 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Basedir' => array ('regex:/^([a-zA-Z0-9\_\.\-\/\:]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$vhost = new Vhost ();
		$vhost->uid = Input::get ('uid');
		$vhost->docroot = Input::get ('docroot');
		$vhost->servername = Input::get ('servername');
		$vhost->serveralias = Input::get ('serveralias');
		$vhost->serveradmin = Input::get ('serveradmin');
		$vhost->basedir = Input::get ('basedir');
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost aangemaakt', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($vhost)
	{
		$users = array ();
		$alerts = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		if ($vhost->locked)
			$alerts[] = new Alert ('Deze vHost is vergrendeld en kan niet door de gebruiker zelf worden bewerkt.', 'warning');
		
		return view ('staff.website.vhost.edit', compact ('vhost', 'users', 'alerts'));
	}
	
	public function update ($vhost)
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Beheerder' => Input::get ('serveradmin'),
				'Alias' => Input::get ('serveralias'),
				'Basedir' => Input::get ('basedir'),
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi'),
				'Document root' => Input::get ('docroot')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Beheerder' => array ('required', 'email'),
				'Alias' => array ('unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias,' . $vhost->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Basedir' => array ('regex:/^([a-zA-Z0-9\_\.\-\/\:]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1'),
				'Document root' => array ('required', 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/website/vhost/' . $vhost->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$vhost->uid = Input::get ('uid');
		$vhost->docroot = Input::get ('docroot');
		$vhost->serveralias = Input::get ('serveralias');
		$vhost->serveradmin = Input::get ('serveradmin');
		$vhost->basedir = Input::get ('basedir');
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost bijgewerkt', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($vhost)
	{
		$vhost->delete ();
		
		Log::log ('vHost verwijderd', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost verwijderd', Alert::TYPE_SUCCESS)));
	}

}
