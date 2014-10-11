<?php

class StaffFtpController extends BaseController
{
	public function index ()
	{
		$ftps = FtpUserVirtual::paginate ();
		
		return View::make ('staff.ftp.index', compact ('ftps'));
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
		
		return View::make ('staff.ftp.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Gebruikersnaam' => Input::get ('user'),
				'Wachtwoord' => Input::get ('passwd'),
				'Wachtwoord (bevestiging)' => Input::get ('passwd_confirm'),
				'Map' => Input::get ('dir')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Gebruikersnaam' => array ('unique:ftp_user_virtual,user', 'alpha_num'),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord',
				'Map' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new FtpUserVirtual ();
		$ftp->uid = Input::get ('uid');
		$userInfo = $ftp->getUser ()->getUserInfo ();
		$ftp->user = (empty (Input::get ('user')) ? $userInfo->username : $userInfo->username . '_' . Input::get ('user'));
		$ftp->setPassword (Input::get ('passwd'));
		$ftp->dir = Input::get ('dir');
		
		$ftp->save ();
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account toegevoegd', 'success')));
	}
	
	public function edit ($ftp)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return View::make ('staff.ftp.edit', compact ('ftp', 'users'));
	}
	
	public function update ($ftp)
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Gebruikersnaam' => Input::get ('user'),
				'Wachtwoord' => Input::get ('passwd'),
				'Wachtwoord (bevestiging)' => Input::get ('passwd_confirm'),
				'Map' => Input::get ('dir')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Gebruikersnaam' => array ('unique:ftp_user_virtual,user', 'alpha_num'),
				'Wachtwoord' => array ('required_with:Wachtwoord (bevestiging)', 'min:8'),
				'Wachtwoord (bevestiging)' => array ('required_with:Wachtwoord', 'same:Wachtwoord'),
				'Map' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$ftp->uid = Input::get ('uid');
		$userInfo = $ftp->getUser ()->getUserInfo ();
		$ftp->user = (empty (Input::get ('user')) ? $userInfo->username : $userInfo->username . '_' . Input::get ('user'));
		$ftp->dir = Input::get ('dir');
		if (! empty (Input::get ('passwd')))
			$ftp->setPassword (Input::get ('passwd'));
		
		$ftp->save ();
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account bijgewerkt', 'success')));
	}
	
	public function remove ($ftp)
	{
		$ftp->delete ();
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account verwijderd', 'success')));
	}

}
