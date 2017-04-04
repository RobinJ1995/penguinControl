<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffUserLimitController extends Controller
{
	public function index ($order = 'username')
	{
		$global = UserLimit::whereNull ('uid')->first ();
		$limits = UserLimit::join ('user', 'user.uid', '=', 'user_limit.uid')->join ('user_info', 'user_info.id', '=', 'user.user_info_id')->whereNotNull ('user_limit.uid')->orderBy ($order)->paginate ();
		
		$url = action ('StaffUserLimitController@index');
		
		return view ('staff.user.limit.index', compact ('global', 'limits', 'url'));
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
		
		return view ('staff.user.limit.create', compact ('user', 'users'));
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
		$limit->ftp = Input::get ('ftp');
		$limit->vhost = Input::get ('vhost');
		$limit->mail_domain = Input::get ('maildomain');
		$limit->mail_user = Input::get ('mailuser');
		$limit->mail_forward = Input::get ('mailforwarding');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		Log::log ('Gebruikerslimiet aangemaakt', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering toegevoegd', Alert::TYPE_SUCCESS)));
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
		
		return view ('staff.user.limit.edit', compact ('limit', 'users'));
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
		
		$limit->ftp = Input::get ('ftp');
		$limit->apache_vhost_virtual = Input::get ('vhost');
		$limit->mail_domain_virtual = Input::get ('maildomain');
		$limit->mail_user_virtual = Input::get ('mailuser');
		$limit->mail_forwarding_virtual = Input::get ('mailforwarding');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		Log::log ('Gebruikerslimiet bijgewerkt', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($limit)
	{
		$limit->delete ();
		
		Log::log ('Gebruiker verwijderd', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Uitzondering verwijderd', Alert::TYPE_SUCCESS)));
	}
}