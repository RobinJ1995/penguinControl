<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class MailForwardController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$mFwds = MailForward::where ('uid', $user->uid)
			->with ('mailDomain')
			->get ();
		
		return view ('mail.forward.index', compact ('user', 'userInfo', 'mFwds'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailForward::allowNew ($user))
			return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailForward::getLimit ($user) . ' forwarding addresses.', Alert::TYPE_ALERT)));
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomain::where ('uid', $user->uid)->get ();
		$domains = [];
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('mail.forward.create', compact ('user', 'userInfo', 'domains'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailForward::allowNew ($user))
			return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailForward::getLimit ($user) . ' forwarding addresses.', Alert::TYPE_ALERT)));
		
		$validator = Validator::make
		(
			array
			(
				'E-mail address' => Input::get ('source'),
				'E-mail domain' => Input::get ('domain'),
				'Destination' => Input::get ('destination')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id,uid,' . $user->uid),
				'Destination' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/forward/create')->withInput ()->withErrors ($validator);
		
		$mFwd = new MailForward ();
		$mFwd->uid = $user->uid;
		$mFwd->source = Input::get ('source');
		$mFwd->mail_domain_id = Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		Log::log ('Forwarding address created', $user->id, $mFwd);
		
		return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('Forwarding address created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mFwd)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('You don\'t own this forwarding address!', Alert::TYPE_ALERT)));
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$objDomains = MailDomain::where ('uid', $user->uid)->get ();
		$domains = [];
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('mail.forward.edit', compact ('user', 'userInfo', 'mFwd', 'domains'));
	}
	
	public function update ($mFwd)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'E-mail address' => Input::get ('source'),
				'E-mail domain' => Input::get ('domain'),
				'Destination' => Input::get ('destination')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source,' . $mFwd->id, 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id,uid,' . $user->uid),
				'Destination' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/forward/' . $mFwd->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$mFwd->source = Input::get ('source');
		$mFwd->mail_domain_id = Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		Log::log ('Forwarding address modified', $user->id, $mFwd);
		
		return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('Forwarding address changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mFwd)
	{
		$user = Auth::user ();
		
		$mFwd->delete ();
		
		Log::log ('Forwarding address removed', $user->id, $mFwd);
		
		return Redirect::to ('/mail/forward')->with ('alerts', array (new Alert ('Forwarding address removed', Alert::TYPE_SUCCESS)));
	}

}