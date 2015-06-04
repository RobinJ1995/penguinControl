<?php

class StaffVHostController extends BaseController
{
	public function index ()
	{
		$vhosts = ApacheVhostVirtual::paginate ();
		
		$searchUrl = action ('StaffVHostController@search');
		
		return View::make ('staff.website.vhost.index', compact ('vhosts', 'searchUrl'));
	}
	
	public function search ()
	{
		$host = Input::get ('host');
		$docroot = Input::get ('docroot');
		$basedir = Input::get ('basedir');
		$username = Input::get ('username');
		
		$query = ApacheVhostVirtual::where
			(
				function ($query) use ($host)
				{
					$query->where ('servername', 'LIKE', '%' . $host . '%')
						->orWhere ('serveralias', 'LIKE', '%' . $host . '%');
				}
			)
			->where ('docroot', 'LIKE', '%' . $docroot . '%')
		    	->where ('basedir', 'LIKE', '%' . $basedir . '%');
		
		if (! empty ($username))
		{
			$uid = '';
			
			$users = UserInfo::where ('username', $username);

			$user = $users->first ();
			if (! empty ($user))
			{
				$user = $user->getUser ();
				$uid = $user->uid;
			}
			$query = $query->where ('uid', $uid);
		}
		
		$count = $query->count ();
		$vhosts = $query->paginate ();
		
		$searchUrl = action ('StaffVHostController@search');
		
		return View::make ('staff.website.vhost.search', compact ('count', 'vhosts', 'searchUrl'));
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
		
		return View::make ('staff.website.vhost.create', compact ('users', 'user'));
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
				'Alias' => array ('different:Host', 'unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'), //'vhost_subdomain:' . $ownerUser->userInfo->username),
				'Document root' => array ('required', 'regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Basedir' => array ('regex:/^([a-zA-Z0-9\_\.\-\/\:]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$vhost = new ApacheVhostVirtual ();
		$vhost->uid = Input::get ('uid');
		$vhost->docroot = Input::get ('docroot');
		$vhost->servername = Input::get ('servername');
		$vhost->serveralias = Input::get ('serveralias');
		$vhost->serveradmin = Input::get ('serveradmin');
		$vhost->basedir = Input::get ('basedir');
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost toegevoegd', 'success')));
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
		
		return View::make ('staff.website.vhost.edit', compact ('vhost', 'users', 'alerts'));
	}
	
	public function update ($vhost)
	{
		$user = Auth::user ();
		
		$ownerUser = User::where ('uid', Input::get ('uid'))->firstOrFail ();
		
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
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost bijgewerkt', 'success')));
	}
	
	public function remove ($vhost)
	{
		$vhost->delete ();
		
		return Redirect::to ('/staff/website/vhost')->with ('alerts', array (new Alert ('vHost verwijderd', 'success')));
	}

}
