<?php

class StaffMailDomainController extends BaseController
{
	public function index ()
	{
		$domains = MailDomainVirtual::paginate ();
		
		$searchUrl = action ('StaffMailController@search');
		
		return View::make ('staff.mail.domain.index', compact ('domains', 'searchUrl'));
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
		
		return View::make ('staff.mail.domain.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/create')->withInput ()->withErrors ($validator);
		
		$domain = new MailDomainVirtual ();
		$domain->uid = Input::get ('uid');
		$domain->domain = Input::get ('domain');
		
		$domain->save ();
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein toegevoegd', 'success')));
	}
	
	public function edit ($domain)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return View::make ('staff.mail.domain.edit', compact ('domain', 'users'));
	}
	
	public function update ($domain)
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain,' . $domain->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		
		$domain->domain = Input::get('domain');
		$domain->uid = Input::get ('uid');
		
		$domain->save ();
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein bijgewerkt', 'success')));
	}
	
	public function remove ($domain)
	{
		$domain->delete ();
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein verwijderd', 'success')));
	}

}