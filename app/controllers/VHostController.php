<?php

class VHostController extends BaseController
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		$vhosts = ApacheVhostVirtual::where ('uid', $user->uid)->get ();
		
		return View::make ('website.vhost.index', compact ('user', 'userInfo', 'vhosts'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! ApacheVhostVirtual::allowNew ($user))
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('U mag maximaal ' . ApacheVhostVirtual::getLimit ($user) . ' vHosts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		return View::make ('website.vhost.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! ApacheVhostVirtual::allowNew ($user))
			return Redirect::to ('/website/vhost/create')->withInput ()->with ('alerts', array (new Alert ('U mag maximaal ' . ApacheVhostVirtual::getLimit ($user) . ' vHosts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		$serverName = Input::get ('servername') . '.' . $user->userInfo->username . '.sinners.be';
		
		$validator = Validator::make
		(
			array
			(
				'Host' => $serverName,
				//'Beheerder' => Input::get ('serveradmin'),
				//'Alias' => Input::get ('serveralias'),
				'Document root' => Input::get ('docroot'),
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Host' => array ('required', 'unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/', 'vhost_subdomain:' . $user->userInfo->username),
				//'Beheerder' => array ('required', 'email'),
				//'Alias' => array ('different:Host', 'unique:apache_vhost_virtual,servername', 'unique:apache_vhost_virtual,serveralias', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/', 'vhost_subdomain:' . $user->userInfo->username),
				'Document root' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
				'Protocol' => array ('required', 'in:0,1,2'),
				'CGI' => array ('required', 'in:0,1')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/website/vhost/create')->withInput ()->withErrors ($validator);
		
		$vhost = new ApacheVhostVirtual ();
		$vhost->uid = $user->uid;
		$vhost->docroot = $user->homedir . '/' . Input::get ('docroot');
		$vhost->servername = $serverName;
		$vhost->serveralias = 'www.' . $serverName;
		$vhost->serveradmin = $user->userInfo->username . '@sinners.be';
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		SinLog::log ('vHost aangemaakt', $vhost);
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost toegevoegd', 'success')));
	}
	
	public function edit ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze vHost!', 'alert')));
		
		$insideHomedir = substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir;
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost')->withInput ()->with ('alerts', array (new Alert ('U kan deze vHost niet zelf bewerken. Indien u hier toch graag iets aan zou willen wijzigen, neem dan contact met ons op.', 'alert')));
		
		return View::make ('website.vhost.edit', compact ('user', 'vhost', 'insideHomedir'));
	}
	
	public function update ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze vHost!', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'Document root' => Input::get ('docroot'),
				'Protocol' => Input::get ('ssl'),
				'CGI' => Input::get ('cgi')
			),
			array
			(
				'Document root' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/'),
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
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze vHost!', 'alert')));
		
		if ($vhost->locked)
			return Redirect::to ('/website/vhost/' . $vhost->id . '/edit')->withInput ()->with ('alerts', array (new Alert ('U kan deze vHost niet zelf bewerken. Indien u hier toch graag iets aan zou willen wijzigen, neem dan contact met ons op.', 'alert')));
		
		$insideHomedir = (Input::get ('outsideHomedir') !== 'true');
		
		$vhost->docroot = ($insideHomedir ? $user->homedir : '') . '/' . Input::get ('docroot');
		$vhost->ssl = (int) Input::get ('ssl');
		$vhost->cgi = (bool) Input::get ('cgi');
		
		$vhost->save ();
		
		SinLog::log ('vHost bijgewerkt', $vhost);
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost bijgewerkt', 'success')));
	}
	
	public function remove ($vhost)
	{
		$user = Auth::user ();
		
		if ($vhost->uid !== $user->uid)
			return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze vHost!', 'alert')));
		
		$vhost->delete ();
		
		SinLog::log ('vHost verwijderd', $vhost);
		
		return Redirect::to ('/website/vhost')->with ('alerts', array (new Alert ('vHost verwijderd', 'success')));
	}

}
