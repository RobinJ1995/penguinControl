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

class StaffMailForwardController extends Controller
{
	public function index ()
	{
		$mFwds = MailForward::with ('MailDomain')
			->with ('user')
			->paginate ();
		
		$searchUrl = route ('staff.mail.search');
		
		return view ('staff.mail.forwarding.index', compact ('mFwds', 'searchUrl'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		
		$objDomains = MailDomain::all ();
		$domains = array
		(
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
				'E-mail address' => request ('source'),
				'E-mail domain' => request ('domain'),
				'Destination' => request ('destination')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id'),
				'Destination' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forward/create')->withInput ()->withErrors ($validator);
		
		$domain = MailDomain::findOrFail (request ('domain'));
		
		$mFwd = new MailForward ();
		$mFwd->uid = $domain->uid;
		$mFwd->source = request ('source');
		$mFwd->mail_domain_id = request ('domain');
		$mFwd->destination = request ('destination');
		
		$mFwd->save ();
		
		Log::log ('Forwarding address created', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forward')->with ('alerts', array (new Alert ('Forwarding address added', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mFwd)
	{
		$objDomains = MailDomain::all ();
		$domains = array
		(
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
				'E-mail address' => request ('source'),
				'E-mail domain' => request ('domain'),
				'Destination' => request ('destination')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source,' . $mFwd->id, 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-mail domain' => array ('required', 'exists:mail_domain,id'),
				'Destination' => array ('required', 'email')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/forward/' . $mFwd->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$domain = MailDomain::findOrFail (request ('domain'));
		
		$mFwd->source = request ('source');
		$mFwd->mail_domain_id = $domain->id;
		$mFwd->uid = $domain->uid;
		$mFwd->destination = request ('destination');
		
		$mFwd->save ();
		
		Log::log ('Forwarding address updated', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forward')->with ('alerts', array (new Alert ('Forwarding address updated', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mFwd)
	{
		$mFwd->delete ();
		
		Log::log ('Forwarding address removed', NULL, $mFwd);
		
		return Redirect::to ('/staff/mail/forward')->with ('alerts', array (new Alert ('Forwarding address removed', Alert::TYPE_SUCCESS)));
	}
}
