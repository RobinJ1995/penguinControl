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
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
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
				'E-mailadres' => request ('email'),
				'E-maildomein' => request ('domain'),
				'Wachtwoord' => request ('password'),
				'Wachtwoord (bevestiging)' => request ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user,email', 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				// old mail@domain.com regex: regex:/^[a-zA-Z0-9\.\_\-]+\@[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/
				'E-maildomein' => array ('required', 'exists:mail_domain,id,uid,' . $user->uid),
				'Wachtwoord' => array ('required', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord'
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
		
		Log::log ('E-mailadres aangemaakt', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($mUser)
	{
		$objDomains = MailDomain::all ();
		$domains = array
		(
			//$userInfo->username . '.sinners.be' => '@' . $userInfo->username . '.sinners.be'
		);
		foreach ($objDomains as $objDomain)
			$domains[$objDomain->id] = '@' . $objDomain->domain;
		
		return view ('staff.mail.user.edit', compact ('mUser', 'domains'))->with ('alerts', array (new Alert ('Laat de wachtwoord-velden leeg indien u het huidige wachtwoord niet wenst te wijzigen.', Alert::TYPE_INFO)));
	}
	
	public function update ($mUser)
	{
		$user = Auth::user ();
		
		$validator = Validator::make
		(
			array
			(
				'E-mailadres' => request ('email'),
				'E-maildomein' => request ('domain'),
				'Wachtwoord' => request ('password'),
				'Wachtwoord (bevestiging)' => request ('password_confirm')
			),
			array
			(
				'E-mailadres' => array ('required', 'unique:mail_user,email,' . $mUser->id, 'unique:mail_forward,source', 'regex:/^[a-zA-Z0-9\.\_\-]+$/'),
				'E-maildomein' => array ('required', 'exists:mail_domain,id'),
				'Wachtwoord' => array ('required_with:Wachtwoord (bevestiging)', 'min:8'),
				'Wachtwoord (bevestiging)' => array ('required_with:Wachtwoord', 'same:Wachtwoord')
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
		
		Log::log ('E-mailadres bijgewerkt', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($mUser)
	{
		$mUser->delete ();
		
		Log::log ('E-mailadres verwijderd', NULL, $mUser);
		
		return Redirect::to ('/staff/mail/user')->with ('alerts', array (new Alert ('E-mailaccount verwijderd', Alert::TYPE_SUCCESS)));
	}

}
