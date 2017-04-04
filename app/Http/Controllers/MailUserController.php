<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class MailUserController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$mUsers = MailUser::where ('uid', $user->uid)
			->with ('mailDomain')
			->get ();
		
		return view ('mail.user.index', compact ('user', 'userInfo', 'mUsers', 'alerts'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailUser::allowNew ($user))
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailUser::getLimit ($user) . ' e-mail accounts.', Alert::TYPE_ALERT)));
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomain::where ('uid', $user->uid)->get ();
		$domains = [];
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('mail.user.create', compact ('user', 'userInfo', 'domains'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailUser::allowNew ($user))
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailUser::getLimit ($user) . ' e-mail accounts.', Alert::TYPE_ALERT)));

		$validator = Validator::make
		(
			array
			(
				'E-mail address' => Input::get ('email'),
				'E-mail domain' => Input::get ('domain'),
				'Password' => Input::get ('password'),
				'Password (confirmation)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id,uid,' . $user->uid),
				'Password' => array ('required', 'min:8'),
				'Password (confirmation)' => 'same:Password'
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/user/create')->withInput ()->withErrors ($validator);
		
		$mUser = new MailUser ();
		$mUser->uid = $user->uid;
		$mUser->email = Input::get ('email');
		$mUser->mail_domain_id = Input::get ('domain');
		$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		Log::log ('E-mail account created', $user->id, $mUser);
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mail account created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mUser)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('You don\'t own this e-mail account!', Alert::TYPE_ALERT)));
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomain::where ('uid', $user->uid)->get ();
		$domains = [];
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('mail.user.edit', compact ('user', 'userInfo', 'mUser', 'domains'));
	}
	
	public function update ($mUser)
	{
		$user = Auth::user ();
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('You don\'t own this e-mail account!', Alert::TYPE_ALERT)));
		
		$validator = Validator::make
		(
			array
			(
				'E-mail address' => Input::get ('email'),
				'E-mail domain' => Input::get ('domain'),
				'Password' => Input::get ('password'),
				'Password (confirmation)' => Input::get ('password_confirm')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email,' . $mUser->id, 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id,uid,' . $user->uid),
				'Password' => array ('required_with:Password (confirmation)', 'min:8'),
				'Password (confirmation)' => array ('required_with:Password', 'same:Password')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/user/' . $mUser->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		
		$mUser->email = Input::get ('email');
		$mUser->mail_domain_id = Input::get ('domain');
		if (! empty (Input::get ('password')))
			$mUser->setPassword (Input::get ('password'));
		
		$mUser->save ();
		
		Log::log ('E-mail account modified', $user->id, $mUser);
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mail account changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mUser)
	{
		$user = Auth::user ();
		
		if ($mUser->uid !== $user->uid)
			return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('You don\'t own this e-mail account!', Alert::TYPE_ALERT)));
		
		$mUser->delete ();
		
		Log::log ('E-mail account removed', $user->id, $mUser);
		
		return Redirect::to ('/mail/user')->with ('alerts', array (new Alert ('E-mail account removed', Alert::TYPE_SUCCESS)));
	}

}
