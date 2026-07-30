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

class StaffMailDomainController extends Controller
{
	public function index ()
	{
		$domains = MailDomain::with ('user')
			->paginate ();
		
		$searchUrl = route ('staff.mail.search');
		
		return view ('staff.mail.domain.index', compact ('domains', 'searchUrl'));
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
		
		return view ('staff.mail.domain.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Domain' => request ('domain')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Domain' => array ('required', 'unique:mail_domain,domain', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/create')->withInput ()->withErrors ($validator);
		
		$domain = new MailDomain ();
		$domain->uid = request ('uid');
		$domain->domain = request ('domain');
		
		$domain->save ();
		
		Log::log ('E-mail domain created', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-mail domain added', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($domain)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ')';
		}
		
		return view ('staff.mail.domain.edit', compact ('domain', 'users'));
	}
	
	public function update ($domain)
	{
		$validator = Validator::make
		(
			array
			(
				'Owner' => request ('uid'),
				'Domain' => request ('domain')
			),
			array
			(
				'Owner' => array ('required', 'integer', 'exists:user,uid'),
				'Domain' => array ('required', 'unique:mail_domain,domain,' . $domain->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		
		$domain->domain = request ('domain');
		$domain->uid = request ('uid');
		
		$domain->save ();
		
		Log::log ('E-mail domain updated', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-mail domain updated', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($domain)
	{
		$mUsersCount = MailUser::where('mail_domain_id', $domain->id)
			->count();
		$mFwdsCount = MailForward::where('mail_domain_id', $domain->id)
			->count();
		
		if ($mUsersCount > 0 || $mFwdsCount > 0)
			return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('You still have e-mail addresses and/or forwarding addresses linked to this domain.', Alert::TYPE_ALERT)));
		
		$domain->delete ();
		
		Log::log ('E-mail domain removed', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-mail domain removed', Alert::TYPE_SUCCESS)));
	}

}