<?php

class MailDomainController extends BaseController
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		$domains = MailDomainVirtual::where ('uid', $user->uid)->get ();
		
		return View::make ('mail.domain.index', compact ('user', 'userInfo', 'domains'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailDomainVirtual::allowNew ($user))
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('U mag maximaal ' . MailDomainVirtual::getLimit ($user) . ' e-maildomeinen aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		return View::make ('mail.domain.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailDomainVirtual::allowNew ($user))
			return Redirect::to ('/mail/domain/create')->withInput ()->with ('alerts', array (new Alert ('U mag maximaal ' . MailDomainVirtual::getLimit ($user) . ' e-maildomeinen aanmaken. Indien u er meer nodig heeft, neem dan contact met ons op.', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/domain/create')->withInput ()->withErrors ($validator);
		
		$domain = new MailDomainVirtual ();
		$domain->uid = $user->uid;
		$domain->domain = Input::get ('domain');
		
		$domain->save ();
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-maildomein toegevoegd', 'success')));
	}
	
	public function edit ($domain)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if ($domain->uid !== $user->uid)
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit e-maildomein!', 'alert')));
		
		if (! $user->mailEnabled)
			return Redirect::to ('/mail');
		
		return View::make ('mail.domain.edit', compact ('user', 'userInfo', 'domain'));
	}
	
	public function update ($domain)
	{
		$user = Auth::user ();
		
		if ($domain->uid !== $user->uid)
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit e-maildomein!', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain,' . $domain->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($domain->uid !== $user->uid)
			return Redirect::to ('/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit e-maildomein!', 'alert')));
		
		
		$domain->domain = Input::get('domain');
		
		$domain->save ();
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-maildomein bijgewerkt', 'success')));
	}
	
	public function remove ($domain)
	{
		$user = Auth::user ();
		
		if ($domain->uid !== $user->uid)
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit e-maildomein!', 'alert')));
		
		$mUsersCount = MailUserVirtual::where('mail_domain_virtual_id', $domain->id)
			->count();
		$mFwdsCount = MailForwardingVirtual::where('mail_domain_virtual_id', $domain->id)
			->count();
		
		if ($mUsersCount > 0 || $mFwdsCount > 0)
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('U heeft nog E-mailadressen en/of doorstuuradressen die aan dit domein zijn gekoppeld.', 'alert')));
		
		$domain->delete ();
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-maildomein verwijderd', 'success')));
	}

}