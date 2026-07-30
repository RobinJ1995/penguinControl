<?php

namespace App\Http\Controllers;

use App\Alert;
use App\DatabaseCredentials;
use App\Models\Ftp;
use App\Mail\AccountLoginLink;
use App\Mail\AccountRenewal;
use App\Mail\AccountTemporaryPassword;
use App\Mail\UserAwaitingActivation;
use App\Models\Log;
use App\Models\User;
use App\Models\SystemTask;
use App\Models\UserInfo;
use App\Models\UserLog;
use App\Models\Vhost;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
	public function start ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;

		return view ('user.start', compact ('user', 'userInfo'));
	}

	public function getLogin ()
	{
		if (Auth::check ())
			return Redirect::to ('/user/start');
		else
			return view ('user.login');
	}

	public function login ()
	{
		$alerts = array ();

		$validator = Validator::make // http://laravel.com/docs/validation //
		(
			array
			(
				'Username' => request ('username'),
				'Password' => request ('password')
			),
			array
			(
				'Username' => 'required',
				'Password' => 'required'
			)
		);

		if ($validator->fails ())
			return view ('user.login')->withErrors ($validator);

		$userInfo = UserInfo::where ('username', request ('username'))->first ();
		if (empty ($userInfo))
			return view ('user.login')->with ('alerts', array (new Alert ('Invalid username', Alert::TYPE_ALERT)));

		$user = User::where ('user_info_id', $userInfo->id)->first ();
		if (empty ($user))
			return view ('user.login')->with ('alerts', array (new Alert ('Your account has not yet been activated.', Alert::TYPE_ALERT)));

		$hashedPass = crypt (request ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
		{
			Log::log ('Login attempt with wrong password', $user->id, $user, $_SERVER['REMOTE_ADDR']);

			return view ('user.login')
				->withInput (request ()->only ('username'))
				->with ('alerts', array (new Alert ('Invalid password for user ' . $userInfo->username, Alert::TYPE_ALERT)));
		}

		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire <= $now && $user->expire != -1)
			return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Your account has expired. Please renew your account to continue,', Alert::TYPE_INFO)));

		Auth::login ($user);

		$hash = DatabaseCredentials::getHash (request ('password'));
		if (! empty ($hash))
			DatabaseCredentials::forUserPrimary_hash ($userInfo->username, $hash);

		$alerts[] = new Alert ('Welcome, ' . $userInfo->fname . '!', Alert::TYPE_SUCCESS);

		$expiresIn = $user->expire - $now;
		if ($expiresIn <= 14 && $user->expire != -1)
			$alerts[] = new Alert ('Warning: Your account will expire in ' . $expiresIn . ' days. <a href="/user/' . $user->id . '/expired">Click here</a> to renew your account.', Alert::TYPE_WARNING);

		Log::log ('User logged in', $user->id, $user, $_SERVER['REMOTE_ADDR']);

		return Redirect::to ('/user/start')->with ('alerts', $alerts);
	}

	public function edit ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;

		return view ('user.edit', compact ('user', 'userInfo'));
	}

	public function update ()
	{
		$user = Auth::user ();
		$alerts = array ();

		$isLoggedInWithToken = Session::get ('isLoggedInWithToken');

		$validator = Validator::make
		(
			array
			(
				'Shell' => request ('shell'),
				'E-mail address' => request ('email'),
				'Current password' => request ('currentPass'),
				'New password' => request ('newPass'),
				'New password (confirmation)' => request ('newPassConfirm')
			),
			array
			(
				'Shell' => array ('required', 'in:/bin/bash,/usr/bin/fish,/usr/bin/zsh,/bin/false,/usr/bin/tmux'),
				'E-mail address' => array ('required', 'email'),
				'Current password' => ($isLoggedInWithToken === true ? '' : array ('required')),
				'New password' => array ('not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8', ($isLoggedInWithToken === true ? '' : 'different:Current password'),  'required_with:New password (confirmation)'),
				'New password (confirmation)' => array ('same:New password', 'required_with:New password')
			)
		);

		if ($validator->fails ())
			return Redirect::to ('/user/edit')->with ('user', $user)->withErrors ($validator);

		if ($isLoggedInWithToken !== true)
		{
			$hashedPass = crypt (request ('currentPass'), $user->crypt);
			if ($hashedPass !== $user->crypt && !$isLoggedInWithToken)
				return Redirect::to ('/user/edit')->with ('alerts', array (new Alert ('The entered current password is incorrect.', Alert::TYPE_ALERT)));
		}

		$userInfo = $user->userInfo;
		$userInfo->email = request ('email');

		if (! empty (request ('newPass')))
		{
			$user->setPassword (request ('newPass'));
			DatabaseCredentials::forUserPrimary($userInfo->username, request ('newPass'));

			$ftp = Ftp::where ('user', $userInfo->username)->where ('locked', '1')->first ();
			$ftpPasswordChanged = false;
			if (! empty ($ftp))
			{
				$ftp->setPassword (request ('newPass'));
				$ftpPasswordChanged = true;

				$ftp->save ();
			}

			$alerts[] = new Alert ('Please note: Your account password ' . ($ftpPasswordChanged ? ' and main FTP account password have' : 'has') . ' been updated. Other passwords, like e-mail account passwords or other FTP account passwords have not been updated. You will have to do this yourself if you wish to do so.', Alert::TYPE_INFO);

			Log::log ('Password changed', $user->id, compact ('isLoggedInWithToken', 'ftpPasswordChanged'));
		}
		$user->shell = request ('shell');

		$userInfo->save ();
		$user->save ();

		Log::log ('User modified his own account', $user->id, $user, $userInfo, compact ('isLoggedInWithToken'));

		$alerts[] = new Alert ('Account modifications have been saved', Alert::TYPE_SUCCESS);

		return Redirect::to ('/user/start')->with ('alerts', $alerts);
	}

	public function logout ()
	{
		Log::log ('User logged out', Auth::user ()->id);

		Auth::logout ();

		return Redirect::to ('/user/login')->with ('alerts', array (new Alert ('You have been logged out.', Alert::TYPE_SUCCESS)));
	}

	public function getRegister ()
	{
		return view ('user.register');
	}

	public function register ()
	{
		$validator = Validator::make
		(
			array
			(
				'Username' => strtolower (request ('username')),
				'Password' => request ('password'),
				'Password (confirmation)' => request ('password_confirm'),
				'First name' => request ('fname'),
				'Surname' => request ('lname'),
				'E-mail address' => request ('email'),
				'Terms and conditions' => request ('termsAgree')
			),
			array
			(
				'Username' => array ('required', 'alpha_num', 'min:4', 'max:14', 'unique:user_info,username', 'not_in:' . prohibited_usernames (true)),
				'Password' => array ('required', 'not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8'),
				'Password (confirmation)' => 'same:Password',
				'First name' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
				'Surname' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
				'E-mail address' => array ('required', 'email'),
				'Terms and conditions' => array ('required', 'accepted')
			)
		);

		if ($validator->fails ())
			return Redirect::to ('/user/register')->withInput ()->withErrors ($validator);

		$etc = array
		(
			'password' => crypt (request ('password'), '$6$rounds=' . random_int (8000, 12000) . '$' . bin2hex (random_bytes (8)) . '$'),
			'mysql_hash' => DatabaseCredentials::getHash (request ('password'))
		);

		$userInfo = new UserInfo ();
		$userInfo->username = strtolower (request ('username'));
		$userInfo->fname = request ('fname');
		$userInfo->lname = request ('lname');
		$userInfo->email = request ('email');
		$userInfo->schoolnr = request ('rnummer');
		$userInfo->lastchange = time () / 60 / 60 / 24;
		$userInfo->etc = serialize ($etc); // After all the dirty hacks that were pulled with the old SINControl, I'm allowed to sin once too... //
		$userInfo->validated = 0;

		$userInfo->save ();

		Mail::send (new UserAwaitingActivation ($userInfo));

		Log::log ('Account registration', NULL, $userInfo);

		return Redirect::to ('/user/login')->with ('alerts', array (new Alert ('Your account registration has been saved. Once your account has been activated by an administrator you will be notified by e-mail.', Alert::TYPE_SUCCESS)));
	}

	public function getExpired ($user)
	{
		$septemberYet = (idate ('n') >= 9);
		$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);

		return view ('user.expired', compact ('user', 'nextYear'));
	}

	public function expired ($user)
	{
		$validator = Validator::make
		(
			array
			(
				'Username' => request ('username'),
				'Password' => request ('password'),
				'Renew' => request ('renew')
			),
			array
			(
				'Username' => array ('required', 'exists:user_info,username'),
				'Password' => 'required',
				'Renew' => array ('required', 'accepted')
			)
		);

		if ($validator->fails ())
			return view ('user.expired', compact ('user'))->withErrors ($validator);

		$userInfo = UserInfo::where ('username', request ('username'))->first ();
		if (empty ($userInfo))
			return view ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Account information could not be found', Alert::TYPE_ALERT)));

		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire > ($now + 14))
			return view ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Your account is not about to expire yet. Account renewal can only be done less than 14 days before your account is set to expire.', Alert::TYPE_ALERT)));

		$hashedPass = crypt (request ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
			return view ('user.expired', compact ('user'))
				->withInput (request ()->only ('username'))
				->with ('alerts', array (new Alert ('Invalid password for user ' . $userInfo->username, Alert::TYPE_ALERT)));

		$userInfo->generateValidationCode ();
		$userInfo->save ();

		$url = url ('user/' . $user->id . '/expired/renew/' . $userInfo->validationcode);
		Mail::send (new AccountRenewal ($userInfo, $url));

		Log::log ('Account renewal requested', $user->id, $userInfo);

		return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('An e-mail has been sent to ' . $userInfo->email . ' containing further instructions to confirm the renewal of your account.', Alert::TYPE_INFO)));
	}

	public function renew ($user, $validationcode)
	{
		$userInfo = $user->userInfo;

		if ($validationcode == $userInfo->validationcode && (! empty ($userInfo->validationcode)))
		{
			$userLog = new UserLog ();
			$userLog->user_info_id = $userInfo->id;
			$userLog->new = 0;
			$userLog->status = 0; // -1 = Not to be billed // 0 = To be billed // 1 = Billed //

			$userInfo->validationcode = null;

			$septemberYet = (idate ('n') >= 9);
			$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);
			$next1OctUnix = strtotime ('Oct 1,' . $nextYear);
			$next1OctDays = ceil ($next1OctUnix / 60 / 60 / 24);

			$user->expire = $next1OctDays;
			if ($user->shell == '/bin/false')
				$user->shell = '/bin/bash';

			$userLog->save ();
			$userInfo->save ();
			$user->save ();

			$vhosts = Vhost::where ('uid', $user->uid)->get ();
			foreach ($vhosts as $vhost)
				$vhost->save (); // save () checks whether the user has expired //

			$task = new SystemTask ();
			$task->type = SystemTask::TYPE_APACHE_RELOAD;
			$task->save ();

			Log::log ('Account renewed', $user->id, $userInfo, $userLog);

			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Your account has been renewed until 1 October 20' . $nextYear . '!', Alert::TYPE_SUCCESS)));
		}
		else
		{
			Log::log ('Account renewal confirmation code refused', $user->id, $user, $validationcode, $_SERVER['REMOTE_ADDR']);

			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('This link is not valid for user ' . $userInfo->username, Alert::TYPE_ALERT)));
		}
	}

	public function getAmnesia ()
	{
		return view ('user.amnesia');
	}

	public function amnesia ()
	{
		$validator = Validator::make
		(
			array
			(
				'Username/e-mail address' => request ('something')
			),
			array
			(
				'Username/e-mail address' => array ('required')
			)
		);

		if ($validator->fails ())
			return view ('user.amnesia')->withErrors ($validator);

		$something = request ('something');

		$userInfo = UserInfo::where ('username', $something)->orWhere ('email', $something)->first ();
		if (empty ($userInfo))
			return view ('user.amnesia')->with ('alerts', array (new Alert ('Account information not found.', Alert::TYPE_ALERT)));

		$user = $userInfo->getUser ();
		if (empty ($user) || $userInfo->validated == 0)
			return view ('user.amnesia')->with ('alerts', array (new Alert ('Your account has not yet been activated.', Alert::TYPE_ALERT)));

		$now = ceil (time () / 60 / 60 / 24);
		$expired = false;
		if ($user->expire <= $now && $user->expire != -1)
		{
			$expired = true;

			$random = bin2hex (random_bytes (8));
			$user->setPassword ($random); //TODO// This can be abused to change arbitrary users' passwords //
			$user->save ();

			Mail::send (new AccountTemporaryPassword ($userInfo, $random));

			Log::log ('Temporary password sent', $user->id, $user, $_SERVER['REMOTE_ADDR']);

			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('An e-mail containing further instrictions has been sent to ' . $userInfo->email . '.', Alert::TYPE_INFO)));
		}

		$userInfo->generateLoginToken ();
		$userInfo->save ();

		$url = url ('user/' . $user->id . '/amnesia/login/' . $userInfo->logintoken);

		Mail::send (new AccountLoginLink ($userInfo, $url));

		logger ()->info ('Amnesia: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR'] . ($expired ? ' (expired)' : ''));

		Log::log ('One-time login link sent', $user->id, $user, $_SERVER['REMOTE_ADDR']);

		return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('An e-mail containing furter instructions has been sent to ' . $userInfo->email . '.', Alert::TYPE_INFO)));
	}

	public function loginWithToken ($user, $logintoken)
	{
		$userInfo = $user->userInfo;

		if ($logintoken == $userInfo->logintoken && (! empty ($userInfo->logintoken)))
		{
			$userInfo->logintoken = null;
			$userInfo->save ();

			$now = ceil (time () / 60 / 60 / 24);
			if ($user->expire <= $now && $user->expire != -1)
				return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Your account has expired. Renew it to continue.<br />Your username is <kbd>' . $userInfo->username . '</kbd>. If you no longer know your password, <a href="/page/contact">contact us</a>.', Alert::TYPE_INFO)));

			Auth::login ($user);

			Session::put ('isLoggedInWithToken', true);

			$alerts[] = new Alert ('Welcome, ' . $userInfo->fname . '!', Alert::TYPE_SUCCESS);
			$alerts[] = new Alert ('You are logged in via a <em>login token</em>. Remember that the link could only be used once. If you wish, you can change your password via <a href="/user/edit">User &raquo; Modify account</a>.', Alert::TYPE_INFO);

			logger ()->info ('Login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);

			Log::log ('User logged in with a one-time login link', $user->id, $user);

			return Redirect::to ('/user/start')->with ('alerts', $alerts);
		}
		else
		{
			logger ()->info ('Failed attempt to login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);

			Log::log ('One-time login token refused', $user->id, $userInfo, $logintoken, $_SERVER['REMOTE_ADDR']);

			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('The supplied link is not valid for user ' . $userInfo->username, Alert::TYPE_ALERT)));
		}
	}
}
