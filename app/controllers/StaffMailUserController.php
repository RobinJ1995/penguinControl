<?php

class StaffMailUserController extends BaseController
{
	public function index ()
	{
		$mUsers = MailUserVirtual::paginate ();
		
		return View::make ('staff.mail.user.index', compact ('mUsers'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		
		$objDomains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;
		
		return View::make ('staff.mail.user.create', compact ('domains', 'user'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('email') . '@' . Input::get ('domain'),
				'E-maildomein' => Input::get ('domain'),
				'Wachtwoord' => Input::get ('password'),
				'Wachtwoord (bevestiging)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source', 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain,uid,' . $user->uid),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord'
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/user/create')->withInput ()->withErrors ($validator);
		
		$domain = MailUserVirtual::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mUser = new MailUserVirtual ();
		$mUser->uid = $domain->uid;
		$mUser->email = Input::get ('email') . '@' . Input::get ('domain');
		$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount toegevoegd', 'success')));
	}
	
	public function edit ($mUser)
	{
		$objDomains = MailDomainVirtual::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;
		
		return View::make ('staff.mail.user.edit', compact ('mUser', 'domains'))->with ('alerts', array (new Alert ('Laat de wachtwoord-velden leeg indien u het huidige wachtwoord niet wenst te wijzigen.', 'info')));
	}
	
	public function update ($mUser)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('email') . '@' . Input::get ('domain'),
				'E-maildomein' => Input::get ('domain'),
				'Wachtwoord' => Input::get ('password'),
				'Wachtwoord (bevestiging)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email,' . $mUser->id, 'unique:mail_forwarding_virtual,source', 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain'),
				'Wachtwoord' => array ('required_with:Wachtwoord (bevestiging)', 'min:8'),
				'Wachtwoord (bevestiging)' => array ('required_with:Wachtwoord', 'same:Wachtwoord')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/user/' . $mUser->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$domain = MailDomainVirtual::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mUser->email = Input::get ('email') . '@' . Input::get ('domain');
		$mUser->uid = $domain->uid;
		if (! empty (Input::get ('password')))
			$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount bijgewerkt', 'success')));
	}
	
	public function remove ($mUser)
	{
		$mUser->delete ();
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount verwijderd', 'success')));
	}

}
