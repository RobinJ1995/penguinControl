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
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffMailUserController extends Controller
{
	public function index ()
	{
		$mUsers = MailUser::with ('MailDomain')
			->with ('user')
			->paginate ();
		
		$searchUrl = route ('staff.mail.search');
		
		return view ('staff.mail.user.index', compact ('mUsers', 'searchUrl'));
	}
	
	public function create ()
	{
		$user = Auth::user ();
		
		$objDomains = MailDomain::where ('uid', $user->uid)->get ();
		$domains = array
		(
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('staff.mail.user.create', compact ('domains', 'user'));
	}

	public function store ()
	{
		$user = Auth::user ();

		$validator = Validator::make
		(
			array
			(
				'E-mail address' => request ('email'),
				'E-mail domain' => request ('domain'),
				'Password' => request ('password'),
				'Password (confirmation)' => request ('password_confirm')
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
			return Redirect::to ('/staff/mail/user/create')->withInput ()->withErrors ($validator);
		
		$domain = MailUser::where ('domain', request ('domain'))->firstOrFail ();
		
		$mUser = new MailUser ();
		$mUser->uid = $domain->uid;
		$mUser->email = request ('email');
		$mUser->mail_domain_id = request ('domain');
		$mUser->setPassword (request ('password'));
		
		$mUser->save ();
		
		Log::log ('E-mail address created', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mail account added', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mUser)
	{
		$objDomains = MailDomain::all ();
		$domains = array
		(
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('staff.mail.user.edit', compact ('mUser', 'domains'))->with ('alerts', array (new Alert ('Leave the password fields empty if you do not wish to change the current password.', Alert::TYPE_INFO)));
	}
	
	public function update ($mUser)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'E-mail address' => request ('email'),
				'E-mail domain' => request ('domain'),
				'Password' => request ('password'),
				'Password (confirmation)' => request ('password_confirm')
			),
			array
			(
				'E-mail address' => array ('required', 'unique:mail_user,email,' . $mUser->id, 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				'E-mail domain' => array ('required', 'exists:mail_domain,id'),
				'Password' => array ('required_with:Password (confirmation)', 'min:8'),
				'Password (confirmation)' => array ('required_with:Password', 'same:Password')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/user/' . $mUser->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		$domain = MailDomain::findOrFail (request ('domain'));
		
		$mUser->email = request ('email');
		$mUser->mail_domain_id = request ('domain');
		$mUser->uid = $domain->uid;
		if (! empty (request ('password')))
			$mUser->setPassword (request ('password'));
		
		$mUser->save ();
		
		Log::log ('E-mail address updated', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mail account updated', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mUser)
	{
		$mUser->delete ();
		
		Log::log ('E-mail address removed', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mail account removed', Alert::TYPE_SUCCESS)));
	}

}
