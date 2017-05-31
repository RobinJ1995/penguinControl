<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Ftp;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class FtpController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		$ftps = Ftp::accessible ()->get ();
		
		return view ('ftp.index', compact ('user', 'userInfo', 'ftps'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! Ftp::allowNew ($user))
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('You are only allowed to create ' . Ftp::getLimit ($user) . ' FTP accounts.', Alert::TYPE_ALERT)));
		
		return view ('ftp.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! Ftp::allowNew ($user))
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('You are only allowed to create ' . Ftp::getLimit ($user) . ' FTP accounts.', Alert::TYPE_ALERT)));
		
		$dir = @ltrim (trailing_slash (Input::get ('dir')), '/');
		$username = $user->userInfo->username . '_' . Input::get ('username');
		if (is_admin () && $user->userInfo->username . '_' == $username)
			$username = $user->userInfo->username;
		
		$validator = Validator::make
		(
			array
			(
				'Username' => $username,
				'Password' => Input::get ('passwd'),
				'Password (confirmation)' => Input::get ('passwd_confirm'),
				'Directory' => $dir
			),
			array
			(
				'Username' => array ('required', 'unique:ftp,username', 'alpha_dash', 'not_in:' . $ftp->user->userInfo->username . '_,' . prohibited_usernames (true)),
				'Password' => array ('required', 'min:8'),
				'Password (confirmation)' => 'same:Password',
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-]+\/)*$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new Ftp ();
		$ftp->uid = $user->uid;
		$ftp->username = $username;
		$ftp->setPassword (Input::get ('passwd'));
		$ftp->dir = $user->homedir . '/' . $dir;
		
		$ftp->save ();
		
		Log::log ('FTP account created', $user->id, $ftp);
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP account created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($ftp)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		return view ('ftp.edit', compact ('user', 'userInfo', 'ftp'));
	}
	
	public function update ($ftp)
	{
		$user = Auth::user ();
		
		$dir = @ltrim (trailing_slash (Input::get ('dir')), '/');
		$username = $ftp->user->userInfo->username . '_' . Input::get ('username');
		if (is_admin () && $ftp->user->userInfo->username . '_' == $username)
			$username = $ftp->user->userInfo->username;
		
		$validator = Validator::make
		(
			array
			(
				'Username' => $username,
				'Password' => Input::get ('passwd'),
				'Password (confirmation)' => Input::get ('passwd_confirm'),
				'Directory' => $dir
			),
			array
			(
				'Username' => array ('required', 'unique:ftp,username,' . $ftp->id, 'alpha_dash', 'not_in:' . $user->userInfo->username . '_,' . prohibited_usernames (true)),
				'Password' => array ('nullable', 'required_with:Password (confirmation)', 'min:8'),
				'Password (confirmation)' => array ('nullable', 'required_with:Password', 'same:Password'),
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-]+\/)*$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$ftp->username = $username;
		$ftp->dir = $ftp->user->homedir . '/' . $dir;
		if (! empty (Input::get ('passwd')))
		{
			Log::log ('FTP account password changed', $user->id, $ftp);
			
			$ftp->setPassword (Input::get ('passwd'));
		}
		
		$ftp->save ();
		
		Log::log ('FTP account modified', $user->id, $ftp);
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP account changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($ftp)
	{
		$user = Auth::user ();
		
		$ftp->delete ();
		
		Log::log ('FTP account removed', $user->id, $ftp);
		
		return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('FTP account removed', Alert::TYPE_SUCCESS)));
	}

}