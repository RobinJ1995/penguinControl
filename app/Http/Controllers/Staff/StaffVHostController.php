<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
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

class StaffVHostController extends Controller
{
	public function index ()
	{
		$vhosts = Vhost::paginate ();
		
		$searchUrl = route ('staff.vhost.search');
		
		return view ('staff.website.vhost.index', compact ('vhosts', 'searchUrl'));
	}
	
	public function search ()
	{
		$host = request ('host');
		$docroot = request ('docroot');
		$basedir = request ('basedir');
		$username = request ('username');
		
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
		
		$searchUrl = route ('staff.vhost.search');
		
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
		$ownerUser = User::where ('uid', request ('uid'))->firstOrFail ();
		
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Host' => request ('servername'),
				'Administrator' => request ('serveradmin'),
				'Alias' => request ('serveralias'),
				'Document root' => request ('docroot'),
				'Basedir' => request ('basedir'),
				'Protocol' => request ('ssl'),
				'CGI' => request ('cgi')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Host' => array ('required', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Administrator' => array ('required', 'email'),
				'Alias' => array ('different:Host', 'unique:vhost,servername', 'unique:vhost,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+(\s[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+)*$/'), //'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Document root' => array ('required', 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Basedir' => array ('regex:/^([a-zA-Z0-9\_\.\-\/\:]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$vhost = new Vhost ();
		$vhost->uid = request ('uid');
		$vhost->docroot = request ('docroot');
		$vhost->servername = request ('servername');
		$vhost->serveralias = request ('serveralias');
		$vhost->serveradmin = request ('serveradmin');
		$vhost->basedir = request ('basedir');
		$vhost->ssl = (int) request ('ssl');
		$vhost->cgi = (bool) request ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost created', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost added', Alert::TYPE_SUCCESS)));
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
			$alerts[] = new Alert ('This vHost is locked and cannot be edited by the user themselves.', 'warning');
		
		return view ('staff.website.vhost.edit', compact ('vhost', 'users', 'alerts'));
	}
	
	public function update ($vhost)
	{
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Administrator' => request ('serveradmin'),
				'Alias' => request ('serveralias'),
				'Basedir' => request ('basedir'),
				'Protocol' => request ('ssl'),
				'CGI' => request ('cgi'),
				'Document root' => request ('docroot')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Administrator' => array ('required', 'email'),
				'Alias' => array ('unique:vhost,servername', 'unique:vhost,serveralias,' . $vhost->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
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
		
		$vhost->uid = request ('uid');
		$vhost->docroot = request ('docroot');
		$vhost->serveralias = request ('serveralias');
		$vhost->serveradmin = request ('serveradmin');
		$vhost->basedir = request ('basedir');
		$vhost->ssl = (int) request ('ssl');
		$vhost->cgi = (bool) request ('cgi');
		
		$vhost->save ();
		
		Log::log ('vHost updated', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost updated', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($vhost)
	{
		$vhost->delete ();
		
		Log::log ('vHost removed', NULL, $vhost);
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost removed', Alert::TYPE_SUCCESS)));
	}

}
