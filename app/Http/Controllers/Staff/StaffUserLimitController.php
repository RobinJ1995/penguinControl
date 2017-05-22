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
	public function index ($order = 'id')
	{
		$global = UserLimit::whereNull ('uid')->first ();
		$limits = UserLimit::whereNotNull ('user_limit.uid')->orderBy ($order)->paginate ();
		
		$url = action ('Staff\StaffUserLimitController@index', $order);
		
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
				'User' => Input::get ('uid'),
				'FTP accounts' => Input::get ('ftp'),
				'vHosts' => Input::get ('vhost'),
				'E-mail domains' => Input::get ('maildomain'),
				'E-mail accounts' => Input::get ('mailuser'),
				'Forwarding addresses' => Input::get ('mailforward'),
				'Storage space' => Input::get ('diskusage')
			),
			array
			(
				'User' => array ('required', 'integer', 'exists:user,uid', 'unique:user_limit,uid'),
				'FTP accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'vHosts' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mail domains' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mail accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'Forwarding addresses' => array ('required', 'integer', 'min:0', 'max:25'),
				'Storage space' => array ('required', 'integer', 'min:10', 'max:1000000')
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
		$limit->mail_forward = Input::get ('mailforward');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		Log::log ('User limit exception created', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Exception created', Alert::TYPE_SUCCESS)));
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
				'FTP accounts' => Input::get ('ftp'),
				'vHosts' => Input::get ('vhost'),
				'E-mail domains' => Input::get ('maildomain'),
				'E-mail accounts' => Input::get ('mailuser'),
				'Forwarding addresses' => Input::get ('mailforward'),
				'Storage space' => Input::get ('diskusage')
			),
			array
			(
				'FTP accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'vHosts' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mail domains' => array ('required', 'integer', 'min:0', 'max:25'),
				'E-mail accounts' => array ('required', 'integer', 'min:0', 'max:25'),
				'Forwarding addresses' => array ('required', 'integer', 'min:0', 'max:25'),
				'Storage space' => array ('required', 'integer', 'min:10', 'max:1000000')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/user/limit/' . $limit->id . '/edit')->withInput ()->withErrors ($validator);
		
		$limit->ftp = Input::get ('ftp');
		$limit->vhost = Input::get ('vhost');
		$limit->mail_domain = Input::get ('maildomain');
		$limit->mail_user = Input::get ('mailuser');
		$limit->mail_forward = Input::get ('mailforward');
		$limit->diskusage = Input::get ('diskusage');
		$limit->save ();
		
		Log::log ('User limit exception modified', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Exception saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($limit)
	{
		$limit->delete ();
		
		Log::log ('User limit exception removed', NULL, $limit);
		
		return Redirect::to ('/staff/user/limit')->with ('alerts', array (new Alert ('Exception removed', Alert::TYPE_SUCCESS)));
	}
}