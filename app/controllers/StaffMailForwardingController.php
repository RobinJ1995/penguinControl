<?php

class StaffMailForwardingController extends BaseController
{
	public function index ()
	{
		$mFwds = MailForwardingVirtual::all ();
		
		return View::make ('staff.mail.forwarding.index', compact ('mFwds'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		
		$objDomains = MailDomainVirtual::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;

		return View::make ('staff.mail.forwarding.create', compact ('domains', 'user'));
	}

	public function store ()
	{
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
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source', 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain'),
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forwarding/create')->withInput ()->withErrors ($validator);
		
		$domain = MailDomainVirtual::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mFwd = new MailForwardingVirtual ();
		$mFwd->uid = $domain->uid;
		$mFwd->source = Input::get ('source') . '@' . Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres toegevoegd', 'success')));
	}
	
	public function edit ($mFwd)
	{
		$objDomains = MailDomainVirtual::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->domain] = '@' . $objDomain->domain;
		
		return View::make ('staff.mail.forwarding.edit', compact ('mFwd', 'domains'));
	}
	
	public function update ($mFwd)
	{
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
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source,' . $mFwd->id, 'email', 'regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/'),
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,domain'),
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/staff/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit doorstuuradres!', 'alert')));
		
		$domain = MailDomainVirtual::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mFwd->source = Input::get ('source') . '@' . Input::get ('domain');
		$mFwd->uid = $domain->uid;
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres bijgewerkt', 'success')));
	}
	
	public function remove ($mFwd)
	{
		$mFwd->delete ();
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres verwijderd', 'success')));
	}
}
