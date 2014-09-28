<?php

class FtpController extends BaseController
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->getUserInfo ();
		$ftps = FtpUserVirtual::where ('uid', $user->uid)->get ();
		
		return View::make ('ftp.index', compact ('user', 'userInfo', 'ftps'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->getUserInfo ();
		
		if (! FtpUserVirtual::allowNew ($user))
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('U mag maximaal ' . FtpUserVirtual::getLimit ($user) . ' FTP-accounts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		return View::make ('ftp.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! FtpUserVirtual::allowNew ($user))
			return Redirect::to ('/ftp/create')->withInput ()->with ('alerts', array (new Alert ('U mag maximaal ' . FtpUserVirtual::getLimit ($user) . ' FTP-accounts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'Gebruikersnaam' => Input::get ('user'),
				'Wachtwoord' => Input::get ('passwd'),
				'Wachtwoord (bevestiging)' => Input::get ('passwd_confirm'),
				'Map' => Input::get ('dir')
			),
			array
			(
				'Gebruikersnaam' => array ('required', 'unique:ftp_user_virtual,user', 'alpha_num'),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord',
				'Map' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new FtpUserVirtual ();
		$ftp->uid = $user->uid;
		$ftp->user = $user->getUserInfo ()->username . '_' . Input::get ('user');
		$ftp->setPassword (Input::get ('passwd'));
		$ftp->dir = $user->homedir . '/' . Input::get ('dir');
		
		$ftp->save ();
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP-account toegevoegd', 'success')));
	}
	
	public function edit ($ftp)
	{
		$user = Auth::user ();
		$userInfo = $user->getUserInfo ();
		
		if ($ftp->locked)
			return Redirect::to ('/ftp')->withInput ()->with ('alerts', array (new Alert ('U kan deze FTP-account niet zelf bewerken. Indien u hier toch graag iets aan zou willen wijzigen, neem dan contact met ons op.', 'alert')));
		
		return View::make ('ftp.edit', compact ('user', 'userInfo', 'ftp'))->with ('alerts', array (new Alert ('Laat de wachtwoord-velden leeg indien u het huidige wachtwoord niet wenst te wijzigen.', 'info')));
	}
	
	public function update ($ftp)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'Gebruikersnaam' => Input::get ('user'),
				'Wachtwoord' => Input::get ('passwd'),
				'Wachtwoord (bevestiging)' => Input::get ('passwd_confirm'),
				'Map' => Input::get ('dir')
			),
			array
			(
				'Gebruikersnaam' => array ('required', 'unique:ftp_user_virtual,user', 'alpha_num'),
				'Wachtwoord' => array ('required_with:Wachtwoord (bevestiging)', 'min:8'),
				'Wachtwoord (bevestiging)' => array ('required_with:Wachtwoord', 'same:Wachtwoord'),
				'Map' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($ftp->uid !== $user->uid)
			return Redirect::to ('/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze FTP-account!', 'alert')));
		
		if ($ftp->locked)
			return Redirect::to ('/ftp/' . $ftp->id . '/edit')->withInput ()->with ('alerts', array (new Alert ('U kan deze FTP-account niet zelf bewerken. Indien u hier toch graag iets aan zou willen wijzigen, neem dan contact met ons op.', 'alert')));
		
		
		$ftp->user = $user->getUserInfo ()->username . '_' . Input::get ('user');
		$ftp->dir = $user->homedir . '/' . Input::get ('dir');
		if (! empty (Input::get ('passwd')))
			$ftp->setPassword (Input::get ('passwd'));
		
		$ftp->save ();
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP-account bijgewerkt', 'success')));
	}
	
	public function remove ($ftp)
	{
		$user = Auth::user ();
		
		if ($ftp->uid !== $user->uid)
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze FTP-account!', 'alert')));
		
		$ftp->delete ();
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP-account verwijderd', 'success')));
	}

}