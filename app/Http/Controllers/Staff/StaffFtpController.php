<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffFtpController extends Controller
{
	public function index ()
	{
		$ftps = Ftp::paginate ();
		
		$searchUrl = route ('staff.ftp.search');
		
		return view ('staff.ftp.index', compact ('ftps', 'searchUrl'));
	}
	
	public function search ()
	{
		$user = request ('user');
		$dir = request ('dir');
		$username = request ('username');
		
		$query = Ftp::where ('dir', 'LIKE', '%' . $dir . '%')
			->where ('username', 'LIKE', '%' . $user . '%');
		
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
		
		$searchUrl = route ('staff.ftp.search');
		
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
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ')';
		}
		
		return view ('staff.ftp.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Username' => request ('user'),
				'Password' => request ('passwd'),
				'Password (confirmation)' => request ('passwd_confirm'),
				'Directory' => request ('dir')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Username' => array ('unique:ftp,username', 'alpha_num'),
				'Password' => array ('required', 'min:8'),
				'Password (confirmation)' => 'same:Password',
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new Ftp ();
		$ftp->uid = request ('uid');
		$userInfo = $ftp->getUser ()->userInfo;
		$ftp->username = (empty (request ('user')) ? $userInfo->username : $userInfo->username . '_' . request ('user'));
		$ftp->setPassword (request ('passwd'));
		$ftp->dir = request ('dir');
		
		$ftp->save ();
		
		Log::log ('FTP account created', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP account added', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($ftp)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ')';
		}
		
		return view ('staff.ftp.edit', compact ('ftp', 'users'));
	}
	
	public function update ($ftp)
	{
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Username' => request ('user'),
				'Password' => request ('passwd'),
				'Password (confirmation)' => request ('passwd_confirm'),
				'Directory' => request ('dir')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Username' => array ('unique:ftp,username', 'alpha_num'),
				'Password' => array ('required_with:Password (confirmation)', 'min:8'),
				'Password (confirmation)' => array ('required_with:Password', 'same:Password'),
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$ftp->uid = request ('uid');
		$userInfo = $ftp->getUser ()->userInfo;
		$ftp->username = (empty (request ('user')) ? $userInfo->username : $userInfo->username . '_' . request ('user'));
		$ftp->dir = request ('dir');
		if (! empty (request ('passwd')))
			$ftp->setPassword (request ('passwd'));
		
		$ftp->save ();
		
		Log::log ('FTP account updated', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP account updated', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($ftp)
	{
		$ftp->delete ();
		
		Log::log ('FTP account removed', NULL, $ftp);
		
		return Redirect::to ('/staff/ftp')->with ('alerts', array (new Alert ('FTP account removed', Alert::TYPE_SUCCESS)));
	}

}
