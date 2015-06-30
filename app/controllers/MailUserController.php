<?php

class MailUserController extends BaseController
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$mUsers = MailUserVirtual::where ('uid', $user->uid)
			->with ('mailDomainVirtual')
			->get ();
		
		$alerts = array (new Alert ('Er zijn momenteel wat problemen met onze mailserver waardoor aangemaakte e-mailaccounts mogelijk niet of niet goed werken. Doorstuuradressen zouden echter wel moeten werken.', 'info'));
		
		return View::make ('mail.user.index', compact ('user', 'userInfo', 'mUsers', 'alerts'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailUserVirtual::allowNew ($user))
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('U mag maximaal ' . MailUserVirtual::getLimit ($user) . ' e-mailaccounts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return View::make ('mail.user.create', compact ('user', 'userInfo', 'domains'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailUserVirtual::allowNew ($user))
			return Redirect::to ('/mail/user/create')->withInput ()->with ('alerts', array (new Alert ('U mag maximaal ' . MailUserVirtual::getLimit ($user) . ' e-mailaccounts aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));

		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('email'),
				'E-maildomein' => Input::get ('domain'),
				'Wachtwoord' => Input::get ('password'),
				'Wachtwoord (bevestiging)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,id,uid,' . $user->uid),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord'
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/user/create')->withInput ()->withErrors ($validator);
		
		$mUser = new MailUserVirtual ();
		$mUser->uid = $user->uid;
		$mUser->email = Input::get ('email');
		$mUser->mail_domain_virtual_id = Input::get ('domain');
		$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mailaccount toegevoegd', 'success')));
	}
	
	public function edit ($mUser)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return View::make ('mail.user.edit', compact ('user', 'userInfo', 'mUser', 'domains'))->with ('alerts', array (new Alert ('Laat de wachtwoord-velden leeg indien u het huidige wachtwoord niet wenst te wijzigen.', 'info')));
	}
	
	public function update ($mUser)
	{
		$user = Auth::user ();
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('email'),
				'E-maildomein' => Input::get ('domain'),
				'Wachtwoord' => Input::get ('password'),
				'Wachtwoord (bevestiging)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email,' . $mUser->id, 'unique:mail_forwarding_virtual,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,id,uid,' . $user->uid),
				'Wachtwoord' => array ('required_with:Wachtwoord (bevestiging)', 'min:8'),
				'Wachtwoord (bevestiging)' => array ('required_with:Wachtwoord', 'same:Wachtwoord')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/user/' . $mUser->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user/' . $mUser->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		
		$mUser->email = Input::get ('email');
		$mUser->mail_domain_virtual_id = Input::get ('domain');
		if (! empty (Input::get ('password')))
			$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mailaccount bijgewerkt', 'success')));
	}
	
	public function remove ($mUser)
	{
		$user = Auth::user ();
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		$mUser->delete ();
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mailaccount verwijderd', 'success')));
	}

}
