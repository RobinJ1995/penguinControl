<?php

namespace App\Http\Controllers;

use App\Alert;
use App\DatabaseCredentials;
use App\Models\Ftp;
use App\Models\Log;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Vhost;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
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
				'Username' => Input::get ('username'),
				'Password' => Input::get ('password')
			),
			array
			(
				'Username' => 'required',
				'Password' => 'required'
			)
		);
		
		if ($validator->fails ())
			return view ('user.login')->withErrors ($validator);
		
		$userInfo = UserInfo::where ('username', Input::get ('username'))->first ();
		if (empty ($userInfo))
			return view ('user.login')->with ('alerts', array (new Alert ('Invalid username', Alert::TYPE_ALERT)));
		
		$user = User::where ('user_info_id', $userInfo->id)->first ();
		if (empty ($user))
			return view ('user.login')->with ('alerts', array (new Alert ('Your account has not yet been activated.', Alert::TYPE_ALERT)));
		
		$hashedPass = crypt (Input::get ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
		{
			Log::log ('Login attempt with wrong password', $user->id, $user, $_SERVER['REMOTE_ADDR']);
			
			return view ('user.login')
				->withInput (Input::only ('username'))
				->with ('alerts', array (new Alert ('Invalid password for user ' . $userInfo->username, Alert::TYPE_ALERT)));
		}
		
		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire <= $now && $user->expire != -1)
			return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Your account has expired. Please renew your account to continue,', Alert::TYPE_INFO)));

		Auth::login ($user);

		$hash = DatabaseCredentials::getHash (Input::get ('password'));
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
				'Shell' => Input::get ('shell'),
				'E-mail address' => Input::get ('email'),
				'Current password' => Input::get ('currentPass'),
				'New password' => Input::get ('newPass'),
				'New password (confirmation)' => Input::get ('newPassConfirm')
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
			$hashedPass = crypt (Input::get ('currentPass'), $user->crypt);
			if ($hashedPass !== $user->crypt && !$isLoggedInWithToken)
				return Redirect::to ('/user/edit')->with ('alerts', array (new Alert ('The entered current password is incorrect.', Alert::TYPE_ALERT)));
		}
		
		$userInfo = $user->userInfo;
		$userInfo->email = Input::get ('email');
		
		if (! empty (Input::get ('newPass')))
		{
			$user->setPassword (Input::get ('newPass'));
			DatabaseCredentials::forUserPrimary($userInfo->username, Input::get ('newPass'));
			
			$ftp = Ftp::where ('user', $userInfo->username)->where ('locked', '1')->first ();
			$ftpPasswordChanged = false;
			if (! empty ($ftp))
			{
				$ftp->setPassword (Input::get ('newPass'));
				$ftpPasswordChanged = true;
				
				$ftp->save ();
			}
			
			$alerts[] = new Alert ('Please note: Your account password ' . ($ftpPasswordChanged ? ' and main FTP account password have' : 'has') . ' been updated. Other passwords, like e-mail account passwords or other FTP account passwords have not been updated. You will have to do this yourself if you wish to do so.', Alert::TYPE_INFO);
			
			Log::log ('Password changed', $user->id, compact ('isLoggedInWithToken', 'ftpPasswordChanged'));
		}
		$user->shell = Input::get ('shell');
		
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
		$reservedUsers = array ('ns', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5', 'sin', 'control', 'sincontrol', 'admin', 'administrator', 'root', 'stamper', 'srv', 'intern', 'extern', 'git', 'svn', 'db', 'database', 'web', 'mail', 'shell', 'cloud', 'voice', 'docu');
		$etcPasswd = explode (PHP_EOL, file_get_contents ('/etc/passwd'));
		
		foreach ($etcPasswd as $entry)
		{
			if (! empty ($entry))
			{
				$fields = explode (':', $entry, 2);

				$reservedUsers[] = $fields[0];
			}
		}
		
		$strReservedUsers = implode (',', $reservedUsers);
		
		$validator = Validator::make
		(
			array
			(
				'Username' => strtolower (Input::get ('username')),
				'Password' => Input::get ('password'),
				'Password (confirmation)' => Input::get ('password_confirm'),
				'First name' => Input::get ('fname'),
				'Surname' => Input::get ('lname'),
				'E-mail address' => Input::get ('email'),
				'Terms and conditions' => Input::get ('termsAgree')
			),
			array
			(
				'Username' => array ('required', 'alpha_num', 'min:4', 'max:14', 'unique:user_info,username', 'not_in:' . $strReservedUsers),
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
			'password' => crypt (Input::get ('password'), '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$'),
			'mysql_hash' => DatabaseCredentials::getHash (Input::get ('password'))
		);
		
		$userInfo = new UserInfo ();
		$userInfo->username = strtolower (Input::get ('username'));
		$userInfo->fname = Input::get ('fname');
		$userInfo->lname = Input::get ('lname');
		$userInfo->email = Input::get ('email');
		$userInfo->schoolnr = Input::get ('rnummer');
		$userInfo->lastchange = time () / 60 / 60 / 24;
		$userInfo->etc = serialize ($etc); // Na al de dirty hacks die Runes uitgehaald heeft met de oude SINControl mag ik ook wel eens zondigen zeker... //
		$userInfo->validated = 0;
		
		$userInfo->save ();
		
		Mail::send ('email.staff.user.awaiting_activation', compact ('userInfo'), function ($msg) use ($userInfo)
			{
				$msg->to (Config::get ('penguin.admin_email', '🐧control')->subject ('User awaiting activation'));
			}
		);
		
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
				'Username' => Input::get ('username'),
				'Password' => Input::get ('password'),
				'Verlengen' => Input::get ('renew')
			),
			array
			(
				'Username' => array ('required', 'exists:user_info,username'),
				'Password' => 'required',
				'Verlengen' => array ('required', 'accepted')
			)
		);
		
		if ($validator->fails ())
			return view ('user.expired', compact ('user'))->withErrors ($validator);
		
		$userInfo = UserInfo::where ('username', Input::get ('username'))->first ();
		if (empty ($userInfo))
			return view ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Account information could not be found', Alert::TYPE_ALERT)));
		
		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire > ($now + 14))
			return view ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Your account is not about to expire yet. Account renewal can only be done less than 14 days before your account is set to expire.', Alert::TYPE_ALERT)));
		
		$hashedPass = crypt (Input::get ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
			return view ('user.expired', compact ('user'))
				->withInput (Input::only ('username'))
				->with ('alerts', array (new Alert ('Invalid password for user ' . $userInfo->username, Alert::TYPE_ALERT)));
		
		$userInfo->generateValidationCode ();
		$userInfo->save ();

		$url = url ('user/' . $user->id . '/expired/renew/' . $userInfo->validationcode);
		Mail::send ('email.user.expired', compact ('userInfo', 'url'), function ($msg) use ($userInfo)
			{
				$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Account renewal');
			}
		);
		
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
			$userLog->nieuw = 0;
			$userLog->boekhouding = 0; // -1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd //
			
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
				$vhost->save (); // In save () wordt nagekeken of user expired is //
			
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
				'Username/e-mail address' => Input::get ('something')
			),
			array
			(
				'Username/e-mail address' => array ('required')
			)
		);
		
		if ($validator->fails ())
			return view ('user.amnesia')->withErrors ($validator);
		
		$something = Input::get ('something');
		
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
			
			$random = bin2hex (openssl_random_pseudo_bytes (8));
			$user->setPassword ($random); //TODO// Dit kan misbruikt worden om wachtwoorden van willekeurige gebruikers te wijzigen //
			$user->save ();
			
			Mail::send ('email.user.amnesia_expired', compact ('userInfo', 'random'), function ($msg) use ($userInfo)
				{
					$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Account login information');
				}
			);
			
			Log::log ('Temporary password sent', $user->id, $user, $_SERVER['REMOTE_ADDR']);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('An e-mail containing further instrictions has been sent to ' . $userInfo->email . '.', Alert::TYPE_INFO)));
		}
		
		$userInfo->generateLoginToken ();
		$userInfo->save ();
		
		$url = url ('user/' . $user->id . '/amnesia/login/' . $userInfo->logintoken);
		
		Mail::send ('email.user.amnesia', compact ('userInfo', 'url'), function ($msg) use ($userInfo)
			{
				$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Account login information');
			}
		);
		
		Log::info ('Amnesia: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR'] . ($expired ? ' (expired)' : ''));
		
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
				return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Uw account is vervallen. Verleng uw account om verder te gaan.<br />Uw gebruikersnaam is <kbd>' . $userInfo->username . '</kbd>. Indien u uw wachtwoord niet meer weet, <a href="/page/contact">neem contact met ons op</a>.', Alert::TYPE_INFO)));
			
			Auth::login ($user);
			
			Session::put ('isLoggedInWithToken', true);

			$alerts[] = new Alert ('Welkom, ' . $userInfo->fname . '!', Alert::TYPE_SUCCESS);
			$alerts[] = new Alert ('U bent ingelogd via een <em>login token</em>. Vergeet niet dat u deze link slechts één keer kon gebruiken. Indien gewenst kunt u uw wachtwoord wijzigen via <a href="/user/edit">Gebruiker &raquo; Gegevens wijzigen</a>.', Alert::TYPE_INFO);
			
			Log::info ('Login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			Log::log ('Gebruiker ingelogd met eenmalige loginlink', $user->id, $user);

			return Redirect::to ('/user/start')->with ('alerts', $alerts);
		}
		else
		{
			Log::info ('Failed attempt to login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			Log::log ('Eenmalige login token geweigerd', $user->id, $userInfo, $logintoken, $_SERVER['REMOTE_ADDR']);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('De opgegeven link is ongeldig voor gebruiker ' . $userInfo->username, Alert::TYPE_ALERT)));
		}
	}
}
