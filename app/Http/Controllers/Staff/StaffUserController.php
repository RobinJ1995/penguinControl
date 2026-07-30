<?php

namespace App\Http\Controllers\Staff;

use App\AppException;
use App\DatabaseCredentials;
use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Mail\AccountActivated;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\Vhost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Alert;
use Illuminate\Support\Facades\Auth;

class StaffUserController extends Controller
{
	public function index ($order = 'uid')
	{
		$now = time () / 60 / 60 / 24;

		//TODO//Paginator::setPageName ('user_page');
		$usersQ = User::where ('expire', '>', $now)
			->orWhere ('expire', -1)
			->orderBy ($order);
		$usersCount = $usersQ->count ();
		$users = $usersQ->paginate ();

		//TODO//Paginator::setPageName ('expired_page');
		$expiredQ = User::where ('expire', '<=', $now)
			->where ('expire', '>', -1)
			->orderBy ($order);
		$expiredCount = $expiredQ->count ();
		$expired = $expiredQ->paginate ();

		//TODO//Paginator::setPageName ('pending_page');
		$pendingQ = UserInfo::where ('validated', 0);
		$pendingCount = $pendingQ->count ();
		$pending = $pendingQ->paginate ();

		$url = route ('staff.user.index');
		$searchUrl = route ('staff.user.search');

		return view ('staff.user.user.index', compact ('usersCount', 'users', 'expiredCount', 'expired', 'pendingCount', 'pending', 'url', 'searchUrl'));
	}

	public function search ()
	{
		$username = request ('username');
		$name = request ('name');
		$email = request ('email');
		$unusedLoginToken = request ('logintoken');

		$query = UserInfo::where ('validated', '1')
			->where ('username', 'LIKE', '%' . $username . '%')
			->where (DB::raw ('CONCAT (fname, " ", lname)'), 'LIKE', '%' . $name . '%')
			->where ('email', 'LIKE', '%' . $email . '%');

		if (! empty ($unusedLoginToken))
			$query = $query->whereNotNull ('logintoken');

		$count = $query->count ();
		$results = $query->paginate ();

		$searchUrl = route ('staff.user.search');

		return view ('staff.user.user.search', compact ('count', 'results', 'searchUrl'));
	}

	public function create ()
	{
		$uid = User::max ('uid') + 1;
		$groups = Group::all ();

		return view ('staff.user.user.create', compact ('uid', 'groups'));
	}

	public function store ()
	{
		$alerts = array ();

		try
		{
			DB::beginTransaction ();

			$uid = User::max ('uid') + 1;

			$inputHomedir = rtrim (request ('homedir'), '/');

			// This used to be a third copy of the list, disagreeing with the other two and
			// reserving names belonging to the original deployment //
			$strReservedUsers = prohibited_usernames (true);
			$strSecondaryGroups = implode (',', (array) request ('groups'));

			$validator = Validator::make
			(
				array
				(
					'UID' => request ('uid'),
					'Username' => request ('username'),
					'Home directory' => $inputHomedir,
					'E-mail address' => request ('email'),
					'First name' => request ('fname'),
					'Surname' => request ('lname'),
					'Shell' => request ('shell'),
					'E-mail' => request ('mailEnabled'),
					'Password' => request ('password'),
					'Password (confirmation)' => request ('password_confirm'),
					'Primary group' => request ('groupPrimary'),
					'Groups' => request ('groups')
				),
				array
				(
					'UID' => array ('required', 'unique:user,uid', 'integer', 'min:' . $uid, 'max:' . $uid),
					'Username' => array ('required', 'alpha_num', 'min:4', 'max:14', 'not_in:' . $strReservedUsers, 'unique:user_info,username'),
					'Home directory' => array ('unique:user,homedir', 'regex:/^\/home\/[a-z0-9\/]+$/'),
					'E-mail address' => array ('required', 'email'),
					'First name' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Surname' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Shell' => array ('required', allowed_shells_rule ()),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Password' => array ('required', 'not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8'),
					'Password (confirmation)' => 'same:Password',
					'Primary group' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groups' => array ('nullable', 'array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/create')->withInput ()->withErrors ($validator);

			$user = new User ();
			$user->uid = request ('uid');
			$user->setPassword (request ('password'));
			$user->gcos = request ('fname') . ' ' . request ('lname') . ', ' . request ('email');
			$user->gid = request ('groupPrimary');
			$user->homedir = $inputHomedir;
			$user->shell = request ('shell');
			$user->lastchange = ceil (time () / 60 / 60 / 24);
			$user->mail_enabled = request ('mailEnabled');
			// -1 means never // Expiry is an administrator's decision now, not an
			// academic year's // See the expire screen //
			$user->expire = -1;

			$userInfo = new UserInfo ();
			$userInfo->username = request ('username');
			$userInfo->fname = request ('fname');
			$userInfo->lname = request ('lname');
			$userInfo->email = request ('email');
			$userInfo->lastchange = ceil (time () / 60 / 60 / 24);
			$userInfo->validated = 1;

			$userInfo->save ();
			$user->user_info_id = $userInfo->id;
			$user->save ();

			$alerts = array
			(
				new Alert ('User created: ' . request ('username'), Alert::TYPE_SUCCESS)
			);

			foreach ((array) request ('groups') as $gid)
			{
				$assoc = new UserGroup ();
				$assoc->uid = $user->uid;
				$assoc->gid = $gid;

				$assoc->save ();

				$group = Group::where ('gid', $gid)->first ();

				$alerts[] = new Alert ('User ' . $userInfo->username . ' assigned to group: ' . ucfirst ($group->name), Alert::TYPE_SUCCESS);
			}

			$ftp = new Ftp (); // User's default FTP account //
			$ftp->username = $userInfo->username;
			$ftp->uid = $user->uid;
			$ftp->passwd = $user->crypt;
			$ftp->dir = $user->homedir;
			$ftp->locked = 1; // Only editable by staff //
			$ftp->save ();

			$alerts[] = new Alert ('FTP account created: ' . $ftp->username, Alert::TYPE_SUCCESS);

			$task = new SystemTask ();
			$task->type = SystemTask::TYPE_HOMEDIR_PREPARE;
			$task->data = json_encode (array ('userInfoId' => $userInfo->id, 'user' => $userInfo->username));
			$task->save ();

			DatabaseCredentials::forUserPrimary (request ('username'), request ('password'));

			DB::commit ();

			Log::log ('User created', NULL, $user, $userInfo);

			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (\Exception $ex) // ->with ('ex', $ex) apparently doesn't work // Serialization of 'Closure' is not allowed //
		{
			DB::rollback ();

			return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('Creating the user failed. All database transactions have been rolled back.', Alert::TYPE_ALERT)));
		}
	}

	public function edit ($user)
	{
		$userInfo = $user->userInfo;
		$groups = Group::all ();

		return view ('staff.user.user.edit', compact ('user', 'userInfo', 'groups'))->with ('alerts', array (new Alert ('Leave the password fields empty if you do not wish to change the current password.', Alert::TYPE_INFO)));
	}

	public function update ($user)
	{
		$alerts = array ();

		try
		{
			DB::beginTransaction ();

			$strSecondaryGroups = implode (',', (array) request ('groups'));

			$validator = Validator::make
			(
				array
				(
					'E-mail address' => request ('email'),
					'First name' => request ('fname'),
					'Surname' => request ('lname'),
					'Shell' => request ('shell'),
					'E-mail' => request ('mailEnabled'),
					'Password' => request ('password'),
					'Password (confirmation)' => request ('password_confirm'),
					'Primary group' => request ('groupPrimary'),
					'Groups' => request ('groups')
				),
				array
				(
					'E-mail address' => array ('required', 'email'),
					'First name' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Surname' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Shell' => array ('required', allowed_shells_rule ()),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Password' => array ('not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8', 'required_with:Password (confirmation)'),
					'Password (confirmation)' => array ('same:Password', 'required_with:Password'),
					'Primary group' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groups' => array ('array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/' . $user->id . '/edit')->withInput ()->withErrors ($validator);

			if (! empty (request ('password')))
			{
				$user->setPassword (request ('password'));
				$user->lastchange = ceil (time () / 60 / 60 / 24);

				$alerts[] = new Alert ('Only the account password was changed. FTP account passwords and the like are stored separately.', Alert::TYPE_INFO);
			}
			$user->gcos = request ('fname') . ' ' . request ('lname') . ', ' . request ('email');
			$user->gid = request ('groupPrimary');
			$user->shell = request ('shell');
			$user->mail_enabled = request ('mailEnabled');

			$userInfo = $user->userInfo;
			$userInfo->fname = request ('fname');
			$userInfo->lname = request ('lname');
			$userInfo->email = request ('email');
			$userInfo->lastchange = ceil (time () / 60 / 60 / 24);

			$userInfo->save ();
			$user->save ();

			$alerts = array
			(
				new Alert ('User updated: ' . $userInfo->username, Alert::TYPE_SUCCESS)
			);

			$allGroups = Group::pluck ('gid');
			$inputGroups = (array) request ('groups');

			foreach ($allGroups as $gid) // Note: this does not cover the primary group //
			{
				$userGroup = UserGroup::where ('uid', $user->uid)->where ('gid', $gid);

				if ($userGroup->count () < 1) // Not a member of the group //
				{
					if (in_array ($gid, $inputGroups)) // Was ticked in the form //
					{
						$assoc = new UserGroup ();
						$assoc->uid = $user->uid;
						$assoc->gid = $gid;

						$assoc->save ();

						$group = Group::where ('gid', $gid)->first ();

						$alerts[] = new Alert ('User ' . $userInfo->username . ' assigned to group: ' . ucfirst ($group->name), Alert::TYPE_SUCCESS);
					}
				}
				else // Already a member of the group //
				{
					if (! in_array ($gid, $inputGroups)) // Not ticked in the form //
					{
						$userGroup->firstOrFail ()->delete ();

						$group = Group::where ('gid', $gid)->first ();

						$alerts[] = new Alert ('User ' . $userInfo->username . ' removed from group: ' . ucfirst ($group->name), Alert::TYPE_SUCCESS);
					}
				}
			}

			DB::commit ();

			Log::log ('User updated', NULL, $user, $userInfo);

			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (\Exception $ex)
		{
			DB::rollback ();

			return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('Updating the user failed. All database transactions have been rolled back.', Alert::TYPE_ALERT)));
		}
	}

	public function remove ($user)
	{
		$alerts = array ();

		if (request ('confirm') === 'pizza') // For something as serious as removing a user, better not to rely on Javascript confirm () alone //
		{
			try
			{
				DB::beginTransaction ();

				$alerts = array ();
				$userInfo = $user->userInfo;

				/*
				 * Removing the rest normally happens through the database's CASCADE DELETE,
				 * but that does not go through the ->remove () method. So they are removed
				 * by hand here, because some entities -- the vHosts, for instance -- carry
				 * custom code in ->remove () that really should run on deletion.
				 */

				foreach (Ftp::where ('uid', $user->uid)->get () as $ftp)
				{
					$ftp->delete ();
					$alerts[] = new Alert ('FTP account removed: ' . $ftp->username, Alert::TYPE_SUCCESS);
				}

				foreach (Vhost::where ('uid', $user->uid)->get () as $vhost)
				{
					$vhost->delete ();
					$alerts[] = new Alert ('vHost removed: ' . $vhost->servername, Alert::TYPE_SUCCESS);
				}

				foreach (MailUser::where ('uid', $user->uid)->get () as $mUser)
				{
					$mUser->delete ();
					$alerts[] = new Alert ('E-mail account removed: ' . $mUser->email, Alert::TYPE_SUCCESS);
				}

				foreach (MailForward::where ('uid', $user->uid)->get () as $mFwd)
				{
					$mFwd->delete ();
					$alerts[] = new Alert ('Forwarding address removed: ' . $mFwd->source, Alert::TYPE_SUCCESS);
				}

				foreach (MailDomain::where ('uid', $user->uid)->get () as $domain)
				{
					$domain->delete ();
					$alerts[] = new Alert ('E-mail domain removed: ' . $domain->domain, Alert::TYPE_SUCCESS);
				}

				$user->delete ();
				//$userInfo->delete ();

				$alerts[] = new Alert ('User removed: ' . $userInfo->username, Alert::TYPE_SUCCESS);

				DB::commit ();

				Log::log ('User removed', NULL, $user, $userInfo);

				return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
			}
			catch (\Exception $ex)
			{
				DB::rollback ();

				return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('Removing the user failed. All database transactions have been rolled back.', Alert::TYPE_ALERT)));
			}
		}
		else
		{
			die ('Removing a user? That is rather drastic. This (somewhat crudely implemented) safeguard is here so that nobody loses their account because a mouse finger slipped and the Javascript `alert ()` dialog did not feel like showing up. If you really do want to go ahead, append `?confirm=pizza` to the URL.');
		}
	}

	public function login ($user)
	{
		$userInfo = $user->userInfo;

		Log::log ('Logged in as user', NULL, $user); // The log entry has to be written before the action itself, or its user_id will be wrong //

		Auth::login ($user);

		return Redirect::to ('/user/start')->with ('alerts', array (new Alert ('Logged in as user: ' . $userInfo->username . ' (' . $userInfo->fname . ' ' . $userInfo->lname . ')')));
	}

	public function getExpire ($user)
	{
		$validUntilUnix = $user->expire * 24 * 60 * 60;
		$validUntilDate = date ('D j F Y', $validUntilUnix);
		$validUntilShortDate = date ('d-m-Y', $validUntilUnix);
		$stillValidUnix = $validUntilUnix - time ();
		$stillValidDate = (int) ($stillValidUnix / 60 / 60 / 24) . ' days';

		$inAYearUnix = strtotime ('+1 year');
		$inAYearDate = date ('D j F Y', $inAYearUnix);

		$nowUnix = time ();
		$nowDate = date ('D j F Y', $nowUnix);

		$expires = array
		(
			$validUntilUnix => 'Current: ' . $validUntilDate,
			$inAYearUnix => 'In a year: ' . $inAYearDate,
			$nowUnix => 'Now: ' . $nowDate,
			-1 * 24 * 60 * 60 => 'Never expires'
		);

		if ($user->expire === -1)
		{
			$validUntilDate = 'Always';
			$validUntilUnix = 'Not applicable';
			$validUntilShortDate = '';
			$stillValidDate = '&infin;';
			$stillValidUnix = '';
		}

		return view ('staff.user.user.expire', compact ('user', 'validUntilUnix', 'validUntilDate', 'stillValidUnix', 'stillValidDate', 'validUntilShortDate', 'expires'));
	}

	public function expire ($user)
	{
		$validator = Validator::make
		(
			array
			(
				'Expiry date' => request ('expire')
			),
			array
			(
				'Expiry date' => array ('integer')
			)
		);

		if ($validator->fails ())
			return Redirect::to ('/staff/user/user/' . $user->id . '/expire')->withInput ()->withErrors ($validator);


		if (request ('expire') > 0)
		{
			$newExpireDays = ceil (request ('expire') / 60 / 60 / 24);
			$newExpireDate = date ('D j F Y', request ('expire'));
		}
		else
		{
			$newExpireDays = -1;
			$newExpireDate = 'Never expires';
		}

		$user->expire = $newExpireDays;
		$user->save ();

		/*
		 * Fetch and re-save all of the user's vHosts: Vhost->save () decides whether
		 * to point the document root at the expired placeholder or at the real one,
		 * based on whether the user has expired.
		 */
		foreach ($user->vhost as $vhost)
			$vhost->save ();

		Log::log ('Expiry date updated', NULL, $user);

		return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('Expiry date for ' . $user->userInfo->username . ' (' . $user->userInfo->getFullName () . ') set to: ' . $newExpireDate)));
	}

	public function getApprove ($userInfo)
	{
		$uid = User::max ('uid') + 1;
		$groups = Group::all ();

		return view ('staff.user.user.validate', compact ('userInfo', 'uid', 'groups'));
	}

	public function approve ($userInfo)
	{
		$alerts = array ();

		try
		{
			if ($userInfo->validated == 1)
				return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('User has already been validated', Alert::TYPE_ALERT)));

			DB::beginTransaction ();

			$uid = User::max ('uid') + 1;

			$inputHomedir = rtrim (request ('homedir'), '/');

			// This used to be a third copy of the list, disagreeing with the other two and
			// reserving names belonging to the original deployment //
			$strReservedUsers = prohibited_usernames (true);

			$strSecondaryGroups = implode (',', (array) request ('groups'));

			$validator = Validator::make
			(
				array
				(
					'UID' => request ('uid'),
					'Username' => request ('username'),
					'Home directory' => $inputHomedir,
					'E-mail address' => request ('email'),
					'First name' => request ('fname'),
					'Surname' => request ('lname'),
					'Shell' => request ('shell'),
					'E-mail' => request ('mailEnabled'),
					'Primary group' => request ('groupPrimary'),
					'Groups' => request ('groups')
				),
				array
				(
					'UID' => array ('required', 'unique:user,uid', 'integer', 'min:' . $uid, 'max:' . $uid),
					'Username' => array ('required', 'alpha_num', 'min:4', 'max:14', 'not_in:' . $strReservedUsers),
					'Home directory' => array ('unique:user,homedir', 'regex:/^\/home\/[^\/]+\/[a-z0-9]\/[a-z0-9]+$/'),
					'E-mail address' => array ('required', 'email'),
					'First name' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Surname' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Shell' => array ('required', allowed_shells_rule ()),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Primary group' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groups' => array ('array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/' . $userInfo->id . '/validate')->withInput ()->withErrors ($validator);

			$etc = unserialize ($userInfo->etc);

			$user = new User ();
			$user->uid = request ('uid');
			$user->crypt = $etc['password'];
			$user->gcos = request ('fname') . ' ' . request ('lname') . ', ' . request ('email');
			$user->gid = request ('groupPrimary');
			$user->homedir = $inputHomedir;
			$user->shell = request ('shell');
			$user->lastchange = time () / 60 / 60 / 24;
			$user->mail_enabled = request ('mailEnabled');
			// -1 means never // See the expire screen for changing it //
			$user->expire = -1;

			$userInfo->username = request ('username');
			$userInfo->fname = request ('fname');
			$userInfo->lname = request ('lname');
			$userInfo->email = request ('email');
			$userInfo->lastchange = time () / 60 / 60 / 24;
			$userInfo->etc = null;
			$userInfo->validated = 1;

			$userInfo->save ();
			$user->user_info_id = $userInfo->id;
			$user->save ();

			$alerts = array
			(
				new Alert ('User created: ' . request ('username'), Alert::TYPE_SUCCESS)
			);

			foreach ((array) request ('groups') as $gid)
			{
				$assoc = new UserGroup ();
				$assoc->uid = $user->uid;
				$assoc->gid = $gid;

				$assoc->save ();

				$group = Group::where ('gid', $gid)->first ();

				$alerts[] = new Alert ('User ' . $userInfo->username . ' assigned to group: ' . ucfirst ($group->name), Alert::TYPE_SUCCESS);
			}

			$vhost = Vhost::makeDefaultFor ($user, $userInfo);

			if ($vhost === NULL)
				$alerts[] = new Alert ('No default vHost was created: penguin.default_vhost_domain is not set.', 'warning');
			else
			{
				$vhost->save ();

				$alerts[] = new Alert ('vHost added: ' . $vhost->servername, Alert::TYPE_SUCCESS);
			}

			$ftp = new Ftp (); // User's default FTP account //
			$ftp->username = $userInfo->username;
			$ftp->uid = $user->uid;
			$ftp->passwd = $user->crypt;
			$ftp->dir = $user->homedir;
			$ftp->locked = 1; // Only editable by staff //
			$ftp->save ();

			$alerts[] = new Alert ('FTP account added: ' . $ftp->username, Alert::TYPE_SUCCESS);

			$task = new SystemTask ();
			$task->type = SystemTask::TYPE_HOMEDIR_PREPARE;
			$task->data = json_encode (array ('userInfoId' => $userInfo->id, 'user' => $userInfo->username));
			$task->save ();

			$alerts[] = new Alert ('The home directory will be created during the next SystemTask run. Don\'t forget to keep an eye on the <a href="/staff/system/systemtask">status</a>.', 'warning');

			DatabaseCredentials::forUserPrimary_hash ($userInfo->username, $etc['mysql_hash']);

			DB::commit ();

			Mail::send (new AccountActivated ($userInfo));

			Log::log ('User validated', NULL, $user, $userInfo);

			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (\Exception $ex)
		{
			DB::rollback ();

			return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('Updating the user failed. All database transactions have been rolled back.', Alert::TYPE_ALERT)));
		}
	}

	public function reject ($userInfo)
	{
		$userInfo->delete ();

		Log::log ('User registration rejected', NULL, $userInfo);

		return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('Validation rejected: ' . $userInfo->username . PHP_EOL . '</br />User information removed.<br />Note: <strong>no</strong> automated e-mail has been sent to the user in question. If this was a real person rather than a bot, please e-mail them yourself and state clearly <strong>why</strong> their registration was rejected.')));
	}

	public function more ($user)
	{
		$userInfo = $user->userInfo;
		$groups = Group::all ();

		try
		{
			$userMailEnabledMap = array
			(
				'0' => 'Disabled',
				'1' => 'Enabled',
				'-1' => 'Blocked'
			);
			$userMailEnabledPretty = $userMailEnabledMap[$user->mail_enabled] . ' (' . $user->mail_enabled . ')';
		}
		catch (\Exception $ex)
		{
			$userMailEnabledPretty = $user->mail_enabled;
		}

		$cryptAlgorithm = explode ('$', $user->crypt)[1];
		switch ($cryptAlgorithm)
		{
			case '1':
				$cryptAlgorithmPretty = 'MD5';
				break;
			case '2a':
				$cryptAlgorithmPretty = 'Blowfish';
				break;
			case '5':
				$cryptAlgorithmPretty = 'SHA-256';
				break;
			case '6':
				$cryptAlgorithmPretty = 'SHA-512';
				break;
			default:
				$cryptAlgorithmPretty = 'Unknown';
		}
		$cryptAlgorithmPretty .= ' ($' . $cryptAlgorithm . '$)';

		return view ('staff.user.user.more', compact ('user', 'userInfo', 'groups', 'userMailEnabledPretty', 'cryptAlgorithmPretty'));
	}

	public function generateLoginToken ($user)
	{
		$userInfo = $user->userInfo;

		$userInfo->generateLoginToken ();
		$userInfo->save ();

		Log::log ('One-time login token generated', NULL, $user, $userInfo);

		return Redirect::to ('staff/user/user/' . $user->id . '/more')->with ('alerts', array (new Alert ('One-time login token generated. It can be passed to the user so that they can set a new password themselves via <em>User</em> -> <em>Modify account</em>.<br />The link expires automatically once it has been used.', Alert::TYPE_INFO)));
	}
}
