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

class StaffMailForwardController extends Controller
{
	public function index ()
	{
		$mFwds = MailForward::with ('MailDomain')
			->with ('user')
			->paginate ();
		
		$searchUrl = action ('Staff\StaffMailController@search');
		
		return view ('staff.mail.forwarding.index', compact ('mFwds', 'searchUrl'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		
		$objDomains = MailDomain::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;

		return view ('staff.mail.forwarding.create', compact ('domains', 'user'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('source'),
				'E-maildomein' => Input::get ('domain'),
				'Bestemming' => Input::get ('destination')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,id'),
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forwarding/create')->withInput ()->withErrors ($validator);
		
		$domain = MailDomain::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mFwd = new MailForward ();
		$mFwd->uid = $domain->uid;
		$mFwd->source = Input::get ('source');
		$mFwd->mail_domain_virtual_id = Input::get ('domain');
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		Log::log ('Doorstuuradres aangemaakt', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mFwd)
	{
		$objDomains = MailDomain::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('staff.mail.forwarding.edit', compact ('mFwd', 'domains'));
	}
	
	public function update ($mFwd)
	{
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => Input::get ('source'),
				'E-maildomein' => Input::get ('domain'),
				'Bestemming' => Input::get ('destination')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user_virtual,email', 'unique:mail_forwarding_virtual,source,' . $mFwd->id, 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-maildomein' => array ('required', 'exists:mail_domain_virtual,id'),
				'Bestemming' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		if ($mFwd->uid !== $user->uid)
			return Redirect::to ('/staff/mail/forwarding/' . $mFwd->id . '/edit')
				->withInput ()
				->with ('alerts', array (new Alert ('U bent niet de eigenaar van dit doorstuuradres!', Alert::TYPE_ALERT)));
		
		$domain = MailDomain::where ('domain', Input::get ('domain'))->firstOrFail ();
		
		$mFwd->source = Input::get ('source') . '@' . Input::get ('domain');
		$mFwd->uid = $domain->uid;
		$mFwd->destination = Input::get ('destination');
		
		$mFwd->save ();
		
		Log::log ('Doorstuuradres bijgewerkt', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mFwd)
	{
		$mFwd->delete ();
		
		Log::log ('Doorstuuradres verwijderd', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forwarding')->with ('alerts', array (new Alert ('Doorstuuradres verwijderd', Alert::TYPE_SUCCESS)));
	}
}
