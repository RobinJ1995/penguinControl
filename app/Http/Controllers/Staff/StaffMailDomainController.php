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

class StaffMailDomainController extends Controller
{
	public function index ()
	{
		$domains = MailDomain::with ('user')
			->paginate ();
		
		$searchUrl = action ('Staff\StaffMailController@search');
		
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
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return view ('staff.mail.domain.create', compact ('user', 'users'));
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain', 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/create')->withInput ()->withErrors ($validator);
		
		$domain = new MailDomain ();
		$domain->uid = Input::get ('uid');
		$domain->domain = Input::get ('domain');
		
		$domain->save ();
		
		Log::log ('E-maildomein aangemaakt', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function edit ($domain)
	{
		$users = array ();
		
		foreach (UserInfo::orderBy ('username')->get () as $objUserInfo)
		{
			$objUser = $objUserInfo->getUser ();
			if (! empty ($objUser))
				$users[$objUser->uid] = $objUserInfo->username . ' (' . $objUserInfo->getFullName () . ', ' . $objUserInfo->schoolnr . ')';
		}
		
		return view ('staff.mail.domain.edit', compact ('domain', 'users'));
	}
	
	public function update ($domain)
	{
		$validator = Validator::make
		(
			array
			(
				'Eigenaar' => Input::get ('uid'),
				'Domein' => Input::get ('domain')
			),
			array
			(
				'Eigenaar' => array ('required', 'integer', 'exists:user,uid'),
				'Domein' => array ('required', 'unique:mail_domain_virtual,domain,' . $domain->id, 'regex:/^[a-zA-Z0-9\.\_\-]+\.[a-zA-Z0-9\.\_\-]+$/')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/mail/domain/' . $domain->id . '/edit')
				->withInput ()
				->withErrors ($validator);
		
		
		$domain->domain = Input::get('domain');
		$domain->uid = Input::get ('uid');
		
		$domain->save ();
		
		Log::log ('E-maildomein bijgewerkt', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein bijgewerkt', Alert::TYPE_SUCCESS)));
	}
	
	public function remove ($domain)
	{
		$mUsersCount = MailUser::where('mail_domain_virtual_id', $domain->id)
			->count();
		$mFwdsCount = MailForward::where('mail_domain_virtual_id', $domain->id)
			->count();
		
		if ($mUsersCount > 0 || $mFwdsCount > 0)
			return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('U heeft nog E-mailadressen en/of doorstuuradressen die aan dit domein zijn gekoppeld.', Alert::TYPE_ALERT)));
		
		$domain->delete ();
		
		Log::log ('E-maildomein verwijderd', NULL, $domain);
		
		return Redirect::to ('/staff/mail/domain')->with ('alerts', array (new Alert ('E-maildomein verwijderd', Alert::TYPE_SUCCESS)));
	}

}