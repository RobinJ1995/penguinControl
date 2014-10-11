<?php

class StaffUserLimitController extends BaseController
{
	public function index ($order = 'username')
	{
		$global = UserLimit::whereNull ('uid')->first ();
		$limits = UserLimit::join ('user', 'user.uid', '=', 'user_limit.uid')->join ('user_info', 'user_info.id', '=', 'user.user_info_id')->whereNotNull ('user_limit.uid')->orderBy ($order)->paginate ();
		
		$url = action ('StaffUserLimitController@index');
		
		return View::make ('staff.user.limit.index', compact ('global', 'limits', 'url'));
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
		
		return View::make ('staff.user.limit.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Gebruiker' => Input::get ('uid'),
				'FTP-accounts' => Input::get ('ftp'),
				'vHosts' => Input::get ('vhost'),
				'E-maildomeinen' => Input::get ('maildomain'),
				'E-mailaccounts' => Input::get ('mailuser'),
				'Doorstuuradressen' => Input::get ('mailforwarding'),
				'Schijfruimte' => Input::get ('diskusage')
			),
			array
			(
				'Gebruiker' => array ('required', 'integer', 'exists:user,uid', 'unique:user_limit,uid'),
				'FTP-accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'vHosts' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-maildomeinen' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mailaccounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'Doorstuuradressen' => array ('required', 'integer', 'min:0', 'max:25'),
				'Schijfruimte' => array ('required', 'integer', 'min:10', 'max:500000')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/user/limit/create')->withInput ()->withErrors ($validator);
		
		$limit = new UserLimit ();
		$limit->uid = Input::get ('uid');
		$limit->ftp_user_virtual = Input::get ('ftp');
		$limit->apache_vhost_virtual = Input::get ('vhost');
		$limit->mail_domain_virtual = Input::get ('maildomain');
		$limit->mail_user_virtual = Input::get ('mailuser');
		$limit->mail_forwarding_virtual = Input::get ('mailforwarding');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering toegevoegd', 'success')));
	}
	
	public function edit ($limit)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return View::make ('staff.user.limit.edit', compact ('limit', 'users'));
	}
	
	public function update ($limit)
	{
		$validator = Validator::make
		(
			array
			(
				'FTP-accounts' => Input::get ('ftp'),
				'vHosts' => Input::get ('vhost'),
				'E-maildomeinen' => Input::get ('maildomain'),
				'E-mailaccounts' => Input::get ('mailuser'),
				'Doorstuuradressen' => Input::get ('mailforwarding'),
				'Schijfruimte' => Input::get ('diskusage')
			),
			array
			(
				'FTP-accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'vHosts' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-maildomeinen' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mailaccounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'Doorstuuradressen' => array ('required', 'integer', 'min:0', 'max:25'),
				'Schijfruimte' => array ('required', 'integer', 'min:10', 'max:500000')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/user/limit/' . $limit->id . '/edit')->withInput ()->withErrors ($validator);
		
		$limit->ftp_user_virtual = Input::get ('ftp');
		$limit->apache_vhost_virtual = Input::get ('vhost');
		$limit->mail_domain_virtual = Input::get ('maildomain');
		$limit->mail_user_virtual = Input::get ('mailuser');
		$limit->mail_forwarding_virtual = Input::get ('mailforwarding');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering bijgewerkt', 'success')));
	}
	
	public function remove ($limit)
	{
		$limit->delete ();
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering verwijderd', 'success')));
	}
}