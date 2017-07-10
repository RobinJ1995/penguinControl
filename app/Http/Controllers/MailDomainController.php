<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class MailDomainController extends Controller
{
	public function index ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		$domains = MailDomain::where ('uid', $user->uid)->get ();
		
		return view ('mail.domain.index', compact ('user', 'userInfo', 'domains'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! MailDomain::allowNew ($user))
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailDomain::getLimit ($user) . ' e-mail domains.', Alert::TYPE_ALERT)));
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		return view ('mail.domain.create', compact ('user', 'userInfo'));
	}

	public function store ()
	{
		$user = Auth::user ();
		
		if (! MailDomain::allowNew ($user))
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('You are only allowed to create ' . MailDomain::getLimit ($user) . ' e-mail domains.', Alert::TYPE_ALERT)));
		
		$validator = Validator::make
		(
			array
			(
				'Domain' => Input::get ('domain')
			),
			array
			(
				'Domain' => array ('required', 'unique:mail_domain,domain', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/domain/create')->withInput ()->withErrors ($validator);
		
		$domain = new MailDomain ();
		$domain->uid = $user->uid;
		$domain->domain = Input::get ('domain');
		
		$domain->save ();
		
		Log::log ('E-mail domain created', $user->id, $domain);
		
		dd (MailDomain::getLastSaved ());
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-mail domein created', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($domain)
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		if (! $user->mail_enabled)
			return Redirect::to ('/mail');
		
		return view ('mail.domain.edit', compact ('user', 'userInfo', 'domain'));
	}
	
	public function update ($domain)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'Domain' => Input::get ('domain')
			),
			array
			(
				'Domain' => array ('required', 'unique:mail_domain,domain,' . $domain->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		
		$domain->domain = Input::get('domain');
		
		$domain->save ();
		
		Log::log ('E-mail domain modified', $user->id, $domain);
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-mail domain changes saved', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($domain)
	{
		$user = Auth::user ();
		
		$mUsersCount = MailUser::where ('mail_domain_id', $domain->id)->count ();
		$mFwdsCount = MailForward::where ('mail_domain_id', $domain->id)->count ();
		
		if ($mUsersCount > 0 || $mFwdsCount > 0)
			return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('You still have e-mail accounts and/or forwarding addresses under this domain.', Alert::TYPE_ALERT)));
		
		$domain->delete ();
		
		Log::log ('E-mail domain removed', $user->id, $domain);
		
		return Redirect::to ('/mail/domain')->with ('alerts', array (new Alert ('E-mail domain removed', Alert::TYPE_SUCCESS)));
	}

}