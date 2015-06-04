<?php

class MailForwardingController extends BaseController
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$mFwds = MailForwardingVirtual::where ('uid', $user->uid)->get ();
		
		return View::make ('mail.forwarding.index', compact ('user', 'userInfo', 'mFwds'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailForwardingVirtual::allowNew ($user))
			return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('U mag maximaal ' . MailForwardingVirtual::getLimit ($user) . ' doorstuuradressen aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;
		
		return View::make ('mail.forwarding.create', compact ('user', 'userInfo', 'domains'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailForwardingVirtual::allowNew ($user))
			return Redirect::to ('/mail/forwarding/create')->withInput ()->with ('alerts', array (new Alert ('U mag maximaal ' . MailForwardingVirtual::getLimit ($user) . ' doorstuuradressen aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('source') . '@' . Input::get ('domain'),
				'E-maildomein' => Input::get ('domain'),
				'Bestemming' => Input::get ('destination')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source', 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/', 'mail_for_uid:' . $user->uid),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain,uid,' . $user->uid),	
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/forwarding/create')->withInput ()->withErrors ($validator);
		
		$mFwd = new MailForwardingVirtual ();
		$mFwd->uid = $user->uid;
		$mFwd->source = Input::get ('source') . '@' . Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres toegevoegd', 'success')));
	}
	
	public function edit ($mFwd)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;
		
		return View::make ('mail.forwarding.edit', compact ('user', 'userInfo', 'mFwd', 'domains'));
	}
	
	public function update ($mFwd)
	{
		$user = Auth::user ();
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('source') . '@' . Input::get ('domain'),
				'E-maildomein' => Input::get ('domain'),
				'Bestemming' => Input::get ('destination')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source,' . $mFwd->id, 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/', 'mail_for_uid:' . $user->uid),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain,uid,' . $user->uid),
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit doorstuuradres!', 'alert')));
		
		
		$mFwd->source = Input::get ('source') . '@' . Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres bijgewerkt', 'success')));
	}
	
	public function remove ($mFwd)
	{
		$user = Auth::user ();
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('U bent niet de eigenaar van deze e-mailaccount!', 'alert')));
		
		$mFwd->delete ();
		
		return Redirect::to ('/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres verwijderd', 'success')));
	}

}