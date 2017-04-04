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

class StaffFtpController extends Controller
{
	public function index ()
	{
		$ftps = Ftp::paginate ();
		
		$searchUrl = action ('Staff\StaffFtpController@search');
		
		return view ('staff.ftp.index', compact ('ftps', 'searchUrl'));
	}
	
	public function search ()
	{
		$user = Input::get ('user');
		$dir = Input::get ('dir');
		$username = Input::get ('username');
		
		$query = Ftp::where ('dir', 'LIKE', '%' . $dir . '%')
			->where ('user', 'LIKE', '%' . $user . '%');
		
		if (! empty ($username))
		{
			$uid = '';
			
			$userInfos = UserInfo::where ('username', 'LIKE', '%' . $username . '%')->get ();
			$uids = array ();
			
			foreach ($userInfos as $userInfo)
			{
				$user = $userInfo->user;
				$uid = $user->uid;
				
				$uids[] = $uid;
			}
			
			$query = $query->whereIn ('uid', $uids);
		}
		
		$count = $query->count ();
		$ftps = $query->paginate ();
		
		$searchUrl = action ('Staff\StaffFtpController@search');
		
		return view ('staff.ftp.search', compact ('count', 'ftps', 'searchUrl'));
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
		
		return view ('staff.ftp.create', compact ('user', 'users'));
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
				'Gebruikersnaam' => array ('unique:ftp,user', 'alpha_num'),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord',
				'Map' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new Ftp ();
		$ftp->uid = Input::get ('uid');
		$userInfo = $ftp->getUser ()->userInfo;
		$ftp->user = (empty (Input::get ('user')) ? $userInfo->username : $userInfo->username . '_' . Input::get ('user'));
		$ftp->setPassword (Input::get ('passwd'));
		$ftp->dir = Input::get ('dir');
		
		$ftp->save ();
		
		Log::log ('FTP-account aangemaakt', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account toegevoegd', Alert::TYPE_SUCCESS)));
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
		
		return view ('staff.ftp.edit', compact ('ftp', 'users'));
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
				'Gebruikersnaam' => array ('unique:ftp,user', 'alpha_num'),
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
		$userInfo = $ftp->getUser ()->userInfo;
		$ftp->user = (empty (Input::get ('user')) ? $userInfo->username : $userInfo->username . '_' . Input::get ('user'));
		$ftp->dir = Input::get ('dir');
		if (! empty (Input::get ('passwd')))
			$ftp->setPassword (Input::get ('passwd'));
		
		$ftp->save ();
		
		Log::log ('FTP-account bijgewerkt', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($ftp)
	{
		$ftp->delete ();
		
		Log::log ('FTP-account verwijderd', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP-account verwijderd', Alert::TYPE_SUCCESS)));
	}

}
