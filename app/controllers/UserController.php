<?php

class UserController extends BaseController
{
	public function start ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		return View::make ('user.start', compact ('user', 'userInfo'));
	}
	
	public function getLogin ()
	{
		if (Auth::check ())
			return Redirect::to ('/user/start');
		else
			return View::make ('user.login');
	}
	
	public function login ()
	{
		$alerts = array ();
		
		$validator = Validator::make // http://laravel.com/docs/validation //
		(
			array
			(
				'Gebruikersnaam' => Input::get ('username'),
				'Wachtwoord' => Input::get ('password')
			),
			array
			(
				'Gebruikersnaam' => 'required',
				'Wachtwoord' => 'required'
			)
		);
		
		if ($validator->fails ())
			return View::make ('user.login')->withErrors ($validator);
		
		$userInfo = UserInfo::where ('username', Input::get ('username'))->first ();
		if (empty ($userInfo))
			return View::make ('user.login')->with ('alerts', array (new Alert ('Ongeldige gebruikersnaam', 'alert')));
		
		$user = User::where ('user_info_id', $userInfo->id)->first ();
		if (empty ($user))
			return View::make ('user.login')->with ('alerts', array (new Alert ('Uw account is nog niet gevalideert.', 'alert')));
		
		$hashedPass = crypt (Input::get ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
		{
			Log::info ('Login attempt with wrong password: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			SinLog::log ('Foute inlogpoging', $user->id, $user, $_SERVER['REMOTE_ADDR']);
			
			return View::make ('user.login')
				->withInput (Input::only ('username'))
				->with ('alerts', array (new Alert ('Ongeldig wachtwoord voor gebruiker ' . $userInfo->username, 'alert')));
		}
		
		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire <= $now && $user->expire != -1)
			return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Uw account is vervallen. Verleng uw account om verder te gaan.', 'info')));

		//if ($user->getLowestGid () > Group::where ('name', 'staff')->firstOrFail ()->gid)
		//	return View::make ('user.login')->with ('alerts', array (new Alert ('Omdat SIN in onderhoud is kunnen gebruikers momenteel niet inloggen.', 'warning')));
		
		Auth::login ($user);

		$hash = DatabaseCredentials::getHash (Input::get ('password'));
		if (! empty ($hash))
			DatabaseCredentials::forUserPrimary_hash ($userInfo->username, $hash);
		
		$alerts[] = new Alert ('Welkom, ' . $userInfo->fname . '!', 'success');
		
		$expiresIn = $user->expire - $now;
		if ($expiresIn <= 14 && $user->expire != -1)
			$alerts[] = new Alert ('Waarschuwing: Uw account zal over ' . $expiresIn . ' dagen vervallen. <a href="/user/' . $user->id . '/expired">Klik hier</a> om uw account te verlengen.', 'warning');
		
		if (! App::environment ('local'))
		{
			// Checken of gebruiker een GitLab account heeft
			// indien niet => maak er één aan
			if ($user->gitLabId == null)
			{
				$git = NULL;
				$gitlabUser = NULL;

				try
				{
					$git = new GitLab ();
					$gitlabUser = $git->createUser ($user->userInfo->email, Input::get ('password'), $user->userInfo->username, $user->userInfo->getFullName ());

					$user->gitLabId = $gitlabUser->id;
					$user->save();
					
					SinLog::log ('GitLab-account aangemaakt bij login', NULL, $gitlabUser, $user);
				}
				catch (Exception $ex)
				{
					$alerts[] = new Alert ('Er is iets misgegaan bij de communicatie met onze Git-server. U zal mogelijk uw account nog niet kunnen gebruiken op <a href="http://git.sinners.be/">onze Git-server</a>. We zullen zo snel mogelijk dit probleem proberen te verhelpen.', 'alert');

					error_send_data ('Fout bij aanmaken Git-account', 'Kan geen Git-account aanmaken voor ' . $userInfo->username . '.', array ($git->getLastCurlInfo (), $gitlabUser));
				}
			}
		}
		else
		{
			$alerts[] = new Alert ('Git-integratie overgeslagen omdat SINControl niet in productieomgeving draait.', 'warn');
			
			SinLog::log ('Git-integratie bij login overgeslagen omdat SINControl in lokale ontwikkelomgeving draait', NULL, App::environment ());
		}
			
		Log::info ('Login: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
		
		SinLog::log ('Gebruiker ingelogd', NULL, $user, $_SERVER['REMOTE_ADDR']);
		
		return Redirect::to ('/user/start')->with ('alerts', $alerts);
	}
	
	public function edit ()
	{
		$user = Auth::user ();
		$userInfo = $user->userInfo;
		
		return View::make ('user.edit', compact ('user', 'userInfo'))->with ('alerts', array (new Alert ('Laat de velden om een nieuw wachtwoord in te stellen leeg indien u uw huidige wachtwoord niet wenst te wijzigen.', 'info')));
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
				'E-mailadres' => Input::get ('email'),
				'Huidige wachtwoord' => Input::get ('currentPass'),
				'Nieuwe wachtwoord' => Input::get ('newPass'),
				'Nieuwe wachtwoord (bevestiging)' => Input::get ('newPassConfirm')
			),
			array
			(
				'Shell' => array ('required', 'in:/bin/bash,/usr/bin/fish,/usr/bin/zsh,/bin/false,/usr/bin/tmux'),
				'E-mailadres' => array ('required', 'email'),
				'Huidige wachtwoord' => ($isLoggedInWithToken === true ? '' : array ('required')),
				'Nieuwe wachtwoord' => array ('not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8', ($isLoggedInWithToken === true ? '' : 'different:Huidige wachtwoord'),  'required_with:Nieuwe wachtwoord (bevestiging)'),
				'Nieuwe wachtwoord (bevestiging)' => array ('same:Nieuwe wachtwoord', 'required_with:Nieuwe wachtwoord')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/user/edit')->with ('user', $user)->withErrors ($validator);
		
		if ($isLoggedInWithToken !== true)
		{
			$hashedPass = crypt (Input::get ('currentPass'), $user->crypt);
			if ($hashedPass !== $user->crypt && !$isLoggedInWithToken)
				return Redirect::to ('/user/edit')->with ('alerts', array (new Alert ('Het ingevoerde huidige wachtwoord is onjuist', 'alert')));
		}
		
		$git = new GitLab();
		
		$userInfo = $user->userInfo;
		$userInfo->email = Input::get ('email');
		$git->changeEmail ($user->gitLabId, Input::get ('email'));
		
		if (! empty (Input::get ('newPass')))
		{
			$user->setPassword (Input::get ('newPass'));
			$git->changePassword($user->gitLabId, Input::get ('newPass'));
			DatabaseCredentials::forUserPrimary($userInfo->username, Input::get ('newPass'));
			
			$ftp = FtpUserVirtual::where ('user', $userInfo->username)->where ('locked', '1')->first ();
			$ftpPasswordChanged = false;
			if (! empty ($ftp))
			{
				$ftp->setPassword (Input::get ('newPass'));
				$ftpPasswordChanged = true;
				
				$ftp->save ();
			}
			
			$alerts[] = new Alert ('Let op: Uw gebruikers-' . ($ftpPasswordChanged ? ', FTP-' : '') . ' en SIN Cloud-wachtwoord zijn aangepast. Andere wachtwoorden (van eventuele andere FTP-accounts of e-mailaccounts) dient u zelf nog te wijzigen indien u dit wenst.', 'info');
			
			Log::info ('Password change: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
		}
		$user->shell = Input::get ('shell');
		
		$userInfo->save ();
		$user->save ();
		
		SinLog::log ('Gebruiker heeft zijn eigen gegevens bijgewerkt', NULL, $user, $userInfo, $isLoggedInWithToken);
		
		$alerts[] = new Alert ('Gegevens bijgewerkt', 'success');
		return Redirect::to ('/user/start')->with ('alerts', $alerts);
	}
	
	public function logout ()
	{
		SinLog::log ('Gebruiker uitgelogd');
		
		Auth::logout ();
		
		return Redirect::to ('/user/login')->with ('alerts', array (new Alert ('U bent uitgelogd')));
	}
	
	public function getRegister ()
	{
		//return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Registraties en verlengingen voor het academiejaar 2014-2015 zijn gesloten.', 'alert')));
		
		return View::make ('user.register');
	}
	
	public function register ()
	{
		//return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Registraties en verlengingen voor het academiejaar 2014-2015 zijn gesloten.', 'alert')));
		
		$reservedUsers = array ('ns', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5', 'sin', 'control', 'sincontrol', 'admin', 'root', 'stamper', 'srv', 'intern', 'extern', 'git', 'svn', 'db', 'database', 'web', 'mail', 'shell', 'cloud', 'voice', 'docu');
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
		
		$rnummer = Input::get ('rnummer');
		if (starts_with ($rnummer, 'c10'))
			$email = $rnummer . '@hik.be';
		else
			$email = $rnummer . '@student.thomasmore.be';
		
		$validator = Validator::make
		(
			array
			(
				'Gebruikersnaam' => strtolower (Input::get ('username')),
				'Wachtwoord' => Input::get ('password'),
				'Wachtwoord (bevestiging)' => Input::get ('password_confirm'),
				'Voornaam' => Input::get ('fname'),
				'Achternaam' => Input::get ('lname'),
				'E-mailadres' => $email, //Input::get ('email'),
				'r-nummer' => Input::get ('rnummer'),
				'Voorwaarden' => Input::get ('termsAgree')
			),
			array
			(
				'Gebruikersnaam' => array ('required', 'alpha_num', 'min:4', 'max:14', 'unique:user_info,username', 'not_in:' . $strReservedUsers),
				'Wachtwoord' => array ('required', 'not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8'),
				'Wachtwoord (bevestiging)' => 'same:Wachtwoord',
				'Voornaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
				'Achternaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
				'E-mailadres' => array ('required', 'email'),
				'r-nummer' => array ('required', 'regex:/^(r\d\d\d\d\d\d\d)|(c10\d\d\d\d\d)$/', 'unique:user_info,schoolnr'),
				'Voorwaarden' => array ('required', 'accepted')
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
		$userInfo->email = $email; //Input::get ('email');
		$userInfo->schoolnr = Input::get ('rnummer');
		$userInfo->lastchange = time () / 60 / 60 / 24;
		$userInfo->etc = serialize ($etc); // Na al de dirty hacks die Runes uitgehaald heeft met de oude SINControl mag ik ook wel eens zondigen zeker... //
		$userInfo->validated = 0;
		
		$userInfo->save ();
		
		Log::info ('Account registration: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
		
		Mail::send ('email.staff.user.awaiting_activation', compact ('userInfo'), function ($msg) use ($userInfo)
			{
				$msg->to ('sinnersstaff@gmail.com', 'SIN')->subject ('Gebruiker wacht op validatie');
			}
		);
		
		SinLog::log ('Gebruikersregistratie', NULL, $userInfo);
		
		return Redirect::to ('/user/login')->with ('alerts', array (new Alert ('Uw registratie is opgeslagen. Uw gegevens zullen door onze medewerkers worden nagekeken om te verifi&euml;ren dat uw wel degelijk een student bent aan Thomas More, waarna u een e-mail zal ontvangen op het opgegeven e-mailadres met verdere instructies voor het activeren van uw account. Dit zal normaalgesproken binnen 24 uur gebeuren. Indien u de e-mail in kwestie niet kan vinden, vergeet dan ook zeker uw spam-folder niet na te kijken. Bij problemen, of indien u na 3 dagen nog steeds geen e-mail van ons heeft ontvangen, <a href="/page/contact">neem gerust contact met ons op</a>.', 'success')));
	}
	
	public function getExpired ($user)
	{
		//return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Registraties en verlengingen voor het academiejaar 2014-2015 zijn gesloten.', 'alert')));
		
		$septemberYet = (idate ('n') >= 9);
		$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);
		
		return View::make ('user.expired', compact ('user', 'nextYear'));
	}
	
	public function expired ($user)
	{
		//return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Registraties en verlengingen voor het academiejaar 2014-2015 zijn gesloten.', 'alert')));
		
		$validator = Validator::make
		(
			array
			(
				'Gebruikersnaam' => Input::get ('username'),
				'Wachtwoord' => Input::get ('password'),
				'Verlengen' => Input::get ('renew')
			),
			array
			(
				'Gebruikersnaam' => array ('required', 'exists:user_info,username'),
				'Wachtwoord' => 'required',
				'Verlengen' => array ('required', 'accepted')
			)
		);
		
		if ($validator->fails ())
			return View::make ('user.expired', compact ('user'))->withErrors ($validator);
		
		$userInfo = UserInfo::where ('username', Input::get ('username'))->first ();
		if (empty ($userInfo))
			return View::make ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Gebruikersinformatie niet gevonden', 'alert')));
		
		$now = ceil (time () / 60 / 60 / 24);
		if ($user->expire > ($now + 14))
			return View::make ('user.expired', compact ('user'))->with ('alerts', array (new Alert ('Uw account staat nog niet op het punt te vervallen en kan dus nog niet verleng worden. Verlengingen kunnen gedaan worden vanaf 14 dagen voor dat de account zal vervallen.', 'alert')));
		
		$hashedPass = crypt (Input::get ('password'), $user->crypt);
		if ($hashedPass !== $user->crypt)
			return View::make ('user.expired', compact ('user'))
				->withInput (Input::only ('username'))
				->with ('alerts', array (new Alert ('Ongeldig wachtwoord voor gebruiker ' . $userInfo->username, 'alert')));
		
		
		if (empty ($userInfo->schoolnr) && empty ($userInfo->email))
		{
			SinLog::log ('Accountverlenging aangevraagd maar mislukt omdat r-nummer of e-mailadres ontbreekt', $user->id, $userInfo);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Geen r-nummer of e-mailadres bekend voor uw account. <a href="/page/contact">Contacteer ons</a>.', 'alert')));
		}
		else
		{
			if (substr (strtolower ($userInfo->schoolnr), 0, 1) == 'r' || substr (strtolower ($userInfo->schoolnr), 0, 1) == 's' || substr (strtolower ($userInfo->email), 0, 2) == 'r0' || substr (strtolower ($userInfo->email), 0, 2) == 's5' || substr (strtolower ($userInfo->schoolnr), 0, 1) == 'p' || substr (strtolower ($userInfo->schoolnr), 0, 1) == 'q')
			{
				$userInfo->validationcode = md5 (time ());
				$userInfo->save ();

				$url = 'https://sinners.be/user/' . $user->id . '/expired/renew/' . $userInfo->validationcode;

				$message = '<p>Beste ' . $userInfo->getFullName () . '</p>' . PHP_EOL
					. PHP_EOL
					. '<p>Er is zojuist een verlenging aangevraagd voor uw SIN-account.<br />' . PHP_EOL
					. 'Om deze verlenging te bevestigen, open de volgende link in uw webbrowser: <a href="' . $url . '">' . $url . '</a></p>' . PHP_EOL
					. '<p>Met vriendelijke groeten<br />' . PHP_EOL
					. 'Het SIN-team</p>';

				$headers = 'From: sin@sinners.be' . "\r\n" .
					   'Content-type: text/html'. "\r\n";

				mail ($userInfo->email, 'Verlenging SIN-account', $message, $headers);
				
				SinLog::log ('Accountverlenging aangevraagd', $user->id, $userInfo);

				return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Er is een e-mail gestuurd naar ' . $userInfo->email . ' met verdere instructies om uw verlenging te bevestigen. Indien u de e-mail in kwestie niet kan terugvinden, vergeet dan zeker uw spam-folder niet na te kijken. Bij problemen, <a href="/page/contact">contacteer ons</a>.', 'info')));
			}
			else
			{
				SinLog::log ('Accountverlenging aangevraagd maar mislukt omdat accountgegevens ontbreken', $user->id, $userInfo);
				
				return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Er ontbreken gegevens voor uw account. Mogelijk is er iets misgegaan bij uw oorspronkelijke registratie. <a href="/page/contact">Contacteer ons</a>. Wij excuseren ons voor het ongemak.', 'alert')));
			}
		}
	}
	
	public function renew ($user, $validationcode)
	{
		//return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Registraties en verlengingen voor het academiejaar 2014-2015 zijn gesloten.', 'alert')));
		
		$userInfo = $user->userInfo;
		
		if ($validationcode == $userInfo->validationcode && (! empty ($userInfo->validationcode)))
		{
			$userLog = new UserLog ();
			$userLog->user_info_id = $userInfo->id;
			$userLog->nieuw = 0;
			$userLog->boekhouding = 0; // -1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd //
			
			if (substr (strtolower ($userInfo->schoolnr), 0, 1) == 'p' || substr (strtolower ($userInfo->schoolnr), 0, 1) == 'q')
				$userLog->boekhouding = -1;
			
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
			
			$vhosts = ApacheVhostVirtual::where ('uid', $user->uid)->get ();
			foreach ($vhosts as $vhost)
				$vhost->save (); // In save () wordt nagekeken of user expired is //
			
			Log::info ('Account renewal: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			SinLog::log ('Account verlengd', $user->id, $userInfo, $userLog);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Uw SIN-account is verlengd tot 1 oktober 20' . $nextYear . '!', 'success')));
		}
		else
		{
			SinLog::log ('Bevestigingscode voor accountverlenging geweigerd', $user->id, $user, $validationcode, $_SERVER['REMOTE_ADDR']);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('De opgegeven link is ongeldig voor gebruiker ' . $userInfo->username, 'alert')));
		}
	}
	
	public function getAmnesia ()
	{
		return View::make ('user.amnesia');
	}
	
	public function amnesia ()
	{
		$validator = Validator::make
		(
			array
			(
				'Gebruikersnaam/e-mailadres/r-nummer' => Input::get ('something')
			),
			array
			(
				'Gebruikersnaam/e-mailadres/r-nummer' => array ('required')
			)
		);
		
		if ($validator->fails ())
			return View::make ('user.amnesia')->withErrors ($validator);
		
		$something = Input::get ('something');
		
		$userInfo = UserInfo::where ('username', $something)->first ();
		if (empty ($userInfo))
		{
			$userInfo = UserInfo::where ('email', $something)->first ();
			if (empty ($userInfo))
			{
				$userInfo = UserInfo::where ('schoolnr', $something)->first ();
				if (empty ($userInfo))
					return View::make ('user.amnesia')->with ('alerts', array (new Alert ('Gebruikersinformatie niet gevonden. <a href="/page/contact">Contacteer ons</a>.', 'alert')));
			}
		}
		
		$user = $userInfo->getUser ();
		if (empty ($user) || $userInfo->validated == 0)
			return View::make ('user.amnesia')->with ('alerts', array (new Alert ('Uw account is nog niet gevalideerd.', 'alert')));
		
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
					$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Inloggegevens SIN-account');
				}
			);
			
			SinLog::log ('Wachtwoord vergeten -- Tijdelijk wachtwoord verstuurd', $user->id, $user, $_SERVER['REMOTE_ADDR']);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Er is een e-mail gestuurd naar ' . $userInfo->email . ' met verdere instructies. Indien u de e-mail in kwestie niet kan terugvinden, vergeet dan zeker uw spam-folder niet na te kijken. Bij problemen, <a href="/page/contact">contacteer ons</a>.', 'info')));
		}
		
		$userInfo->logintoken = md5 (time ());
		$userInfo->save ();
		
		$url = 'https://sinners.be/user/' . $user->id . '/amnesia/login/' . $userInfo->logintoken;
		
		Mail::send ('email.user.amnesia', compact ('userInfo', 'url'), function ($msg) use ($userInfo)
			{
				$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Inloggegevens SIN-account');
			}
		);
		
		Log::info ('Amnesia: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR'] . ($expired ? ' (expired)' : ''));
		
		SinLog::log ('Wachtwoord vergeten -- Eenmalige loginlink verstuurd', $user->id, $user, $_SERVER['REMOTE_ADDR']);
		
		return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('Er is een e-mail gestuurd naar ' . $userInfo->email . ' met verdere instructies. Indien u de e-mail in kwestie niet kan terugvinden, vergeet dan zeker uw spam-folder niet na te kijken. Bij problemen, <a href="/page/contact">contacteer ons</a>.', 'info')));
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
				return Redirect::to ('/user/' . $user->id . '/expired')->with ('alerts', array (new Alert ('Uw account is vervallen. Verleng uw account om verder te gaan.<br />Uw gebruikersnaam is <kbd>' . $userInfo->username . '</kbd>. Indien u uw wachtwoord niet meer weet, <a href="/page/contact">neem contact met ons op</a>.', 'info')));
			
			Auth::login ($user);
			
			Session::put ('isLoggedInWithToken', true);

			$alerts[] = new Alert ('Welkom, ' . $userInfo->fname . '!', 'success');
			$alerts[] = new Alert ('U bent ingelogd via een <em>login token</em>. Vergeet niet dat u deze link slechts één keer kon gebruiken. Indien gewenst kunt u uw wachtwoord wijzigen via <a href="/user/edit">Gebruiker &raquo; Gegevens wijzigen</a>.', 'info');
			
			Log::info ('Login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			SinLog::log ('Gebruiker ingelogd met eenmalige loginlink', $user->id, $user);

			return Redirect::to ('/user/start')->with ('alerts', $alerts);
		}
		else
		{
			Log::info ('Failed attempt to login with token: ' . $userInfo->username . ' from ' . $_SERVER['REMOTE_ADDR']);
			
			SinLog::log ('Eenmalige login token geweigerd', $user->id, $userInfo, $logintoken, $_SERVER['REMOTE_ADDR']);
			
			return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('De opgegeven link is ongeldig voor gebruiker ' . $userInfo->username, 'alert')));
		}
	}
}