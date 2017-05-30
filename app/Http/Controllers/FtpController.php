<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Ftp;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class FtpController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		$ftps = Ftp::where ('uid', $user->uid)->get ();
		
		return view ('ftp.index', compact ('user', 'userInfo', 'ftps'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! Config::get ('penguin.ftp', false))
			return back ()->with ('alerts', array (new Alert ('FTP management has been disabled.', Alert::TYPE_WARNING)));
		else if (! Ftp::allowNew ($user))
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('You are only allowed to create ' . Ftp::getLimit ($user) . ' FTP accounts.', Alert::TYPE_ALERT)));
		
		return view ('ftp.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! Config::get ('penguin.ftp', false))
			return Redirect::to ('/home')->with ('alerts', array (new Alert ('FTP management has been disabled.', Alert::TYPE_WARNING)));
		else if (! Ftp::allowNew ($user))
			return Redirect::to ('/ftp')->with ('alerts', array (new Alert ('You are only allowed to create ' . Ftp::getLimit ($user) . ' FTP accounts.', Alert::TYPE_ALERT)));
		
		$validator = Validator::make
		(
			array
			(
				'Username' => Input::get ('user'),
				'Password' => Input::get ('passwd'),
				'Password (confirmation)' => Input::get ('passwd_confirm'),
				'Directory' => Input::get ('dir')
			),
			array
			(
				'Username' => array ('required', 'unique:ftp,user', 'alpha_num'),
				'Password' => array ('required', 'min:8'),
				'Password (confirmation)' => 'same:Password',
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/create')->withInput ()->withErrors ($validator);
		
		$ftp = new Ftp ();
		$ftp->uid = $user->uid;
		$ftp->user = $user->userInfo->username . '_' . Input::get ('user');
		$ftp->setPassword (Input::get ('passwd'));
		$ftp->dir = $user->homedir . '/' . Input::get ('dir');
		
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
		
		$validator = Validator::make
		(
			array
			(
				'Username' => Input::get ('user'),
				'Password' => Input::get ('passwd'),
				'Password (confirmation)' => Input::get ('passwd_confirm'),
				'Directory' => Input::get ('dir')
			),
			array
			(
				'Username' => array ('required', 'unique:ftp,user', 'alpha_num'),
				'Password' => array ('required_with:Password (confirmation)', 'min:8'),
				'Password (confirmation)' => array ('required_with:Password', 'same:Password'),
				'Directory' => array ('regex:/^([a-zA-Z0-9\_\.\-\/]+)?$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/ftp/' . $ftp->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$ftp->user = $user->userInfo->username . '_' . Input::get ('user');
		$ftp->dir = $user->homedir . '/' . Input::get ('dir');
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