<?php

class StaffUserController extends BaseController
{
	public function index ($order = 'uid')
	{
		$now = time () / 60 / 60 / 24;
		
		Paginator::setPageName ('user_page');
		$usersQ = User::where ('expire', '>', $now)
			->orWhere ('expire', -1)
			->orderBy ($order);
		$usersCount = $usersQ->count ();
		$users = $usersQ->paginate ();
		
		Paginator::setPageName ('expired_page');
		$expiredQ = User::where ('expire', '<=', $now)
			->where ('expire', '>', -1)
			->orderBy ($order);
		$expiredCount = $expiredQ->count ();
		$expired = $expiredQ->paginate ();
		
		Paginator::setPageName ('pending_page');
		$pendingQ = UserInfo::where ('validated', 0);
		$pendingCount = $pendingQ->count ();
		$pending = $pendingQ->paginate ();

		$url = action ('StaffUserController@index');
		$searchUrl = action ('StaffUserController@search');
		
		return View::make ('staff.user.user.index', compact ('usersCount', 'users', 'expiredCount', 'expired', 'pendingCount', 'pending', 'url', 'searchUrl'));
	}
	
	public function search ()
	{
		$username = Input::get ('username');
		$name = Input::get ('name');
		$email = Input::get ('email');
		$schoolnr = Input::get ('schoolnr');
		$gid = Input::get ('gid');
		$unusedValidationCode = Input::get ('validationcode');
		$unusedLoginToken = Input::get ('logintoken');
		
		$query = UserInfo::where ('validated', '1')
			->where ('username', 'LIKE', '%' . $username . '%')
			->where (DB::raw ('CONCAT (fname, " ", lname)'), 'LIKE', '%' . $name . '%')
			->where ('email', 'LIKE', '%' . $email . '%')
			->where ('schoolnr', 'LIKE', '%' . $schoolnr . '%');
		if (! empty ($gid))
			
		if (! empty ($unusedValidationCode))
			$query = $query->whereNotNull ('validationcode');
		if (! empty ($unusedLoginToken))
			$query = $query->whereNotNull ('logintoken');
		
		$count = $query->count ();
		$results = $query->paginate ();
		
		$searchUrl = action ('StaffUserController@search');
		
		return View::make ('staff.user.user.search', compact ('count', 'results', 'searchUrl'));
	}
	
	public function create ()
	{
		$uid = User::max ('uid') + 1;
		$groups = Group::all ();
		
		return View::make ('staff.user.user.create', compact ('uid', 'groups'));
	}

	public function store ()
	{
		$alerts = array ();
		
		try
		{
			DB::beginTransaction ();
			
			$uid = User::max ('uid') + 1;

			$inputHomedir = rtrim (Input::get ('homedir'), '/');

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

			$strSecondaryGroups = implode (',', (array) Input::get ('groups'));

			$validator = Validator::make
			(
				array
				(
					'UID' => Input::get ('uid'),
					'Gebruikersnaam' => Input::get ('username'),
					'Home directory' => $inputHomedir,
					'E-mailadres' => Input::get ('email'),
					'Voornaam' => Input::get ('fname'),
					'Achternaam' => Input::get ('lname'),
					'r-nummer' => Input::get ('rnummer'),
					'Shell' => Input::get ('shell'),
					'E-mail' => Input::get ('mailEnabled'),
					'Wachtwoord' => Input::get ('password'),
					'Wachtwoord (bevestiging)' => Input::get ('password_confirm'),
					'Primaire groep' => Input::get ('groupPrimary'),
					'Groepen' => Input::get ('groups')
				),
				array
				(
					'UID' => array ('required', 'unique:user,uid', 'integer', 'min:' . $uid, 'max:' . $uid),
					'Gebruikersnaam' => array ('required', 'alpha_num', 'min:4', 'max:14', 'not_in:' . $strReservedUsers),
					'Home directory' => array ('unique:user,homedir', 'regex:/^\/home\/[^\/]+\/[a-z0-9]\/[a-z0-9]+$/'),
					'E-mailadres' => array ('required', 'email'),
					'Voornaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Achternaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'r-nummer' => array ('regex:/^(c|r|s|u|q)\d\d\d\d\d\d\d$/'),
					'Shell' => array ('required', 'in:/bin/bash,/usr/bin/fish,/usr/bin/zsh,/bin/false,/usr/bin/tmux'),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Wachtwoord' => array ('required', 'not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8'),
					'Wachtwoord (bevestiging)' => 'same:Wachtwoord',
					'Primaire groep' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groepen' => array ('array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/create')->withInput ()->withErrors ($validator);

			$septemberYet = (idate ('n') >= 9);
			$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);
			$next1OctUnix = strtotime ('Oct 1,' . $nextYear);
			$next1OctDays = ceil ($next1OctUnix / 60 / 60 / 24);

			$user = new User ();
			$user->uid = Input::get ('uid');
			$user->setPassword (Input::get ('password'));
			$user->gcos = Input::get ('fname') . ' ' . Input::get ('lname') . ', ' . Input::get ('email');
			$user->gid = Input::get ('groupPrimary');
			$user->homedir = $inputHomedir;
			$user->shell = Input::get ('shell');
			$user->lastchange = ceil (time () / 60 / 60 / 24);
			$user->mailEnabled = Input::get ('mailEnabled');
			$user->expire = $next1OctDays;

			$userInfo = new UserInfo ();
			$userInfo->username = Input::get ('username');
			$userInfo->fname = Input::get ('fname');
			$userInfo->lname = Input::get ('lname');
			$userInfo->email = Input::get ('email');
			$userInfo->schoolnr = Input::get ('rnummer');
			$userInfo->lastchange = ceil (time () / 60 / 60 / 24);
			$userInfo->validated = 1;

			$userInfo->save ();
			$user->user_info_id = $userInfo->id;
			$user->save ();

			$alerts = array
			(
				new Alert ('Gebruiker aangemaakt: ' . Input::get ('username'), 'success')
			);

			foreach ((array) Input::get ('groups') as $gid)
			{
				$assoc = new UserGroup ();
				$assoc->uid = $user->uid;
				$assoc->gid = $gid;

				$assoc->save ();

				$group = Group::where ('gid', $gid)->first ();

				$alerts[] = new Alert ('Gebruiker ' . $userInfo->username . ' toegewezen aan groep: ' . ucfirst ($group->name), 'success');
			}

			$vhost = new ApacheVhostVirtual (); // User's default vHost //
			$vhost->uid = $user->uid;
			$vhost->docroot = $user->homedir . '/public_html';
			$vhost->servername = $userInfo->username . '.sinners.be';
			$vhost->serveralias = 'www.' . $userInfo->username . '.sinners.be';
			$vhost->serveradmin = $userInfo->username . '@sinners.be';
			$vhost->cgi = 1;
			$vhost->ssl = 0;
			$vhost->locked = 1; // Enkel bewerkbaar door staff //
			$vhost->save ();

			$alerts[] = new Alert ('vHost toegevoegd: ' . $vhost->servername, 'success');

			$ftp = new FtpUserVirtual (); // User's default FTP account //
			$ftp->user = $userInfo->username;
			$ftp->uid = $user->uid;
			$ftp->passwd = $user->crypt;
			$ftp->dir = $user->homedir;
			$ftp->locked = 1; // Enkel bewerkbaar door staff //
			$ftp->save ();

			$alerts[] = new Alert ('FTP-account toegevoegd: ' . $ftp->user, 'success');

			exec ('php /home/users/s/sin/scripts/prepareUserHomedir.php ' . escapeshellarg ($userInfo->username) . ' ' . escapeshellarg ($user->homedir) . ' ' . escapeshellarg ($user->getGroup ()->name), $o);
                        try
                        {
                                $exitStatus1 = $o[0];
                                $exitStatus2 = $o[1];
                                $output = $o[2];

                                if ($exitStatus1 === 0 && $exitStatus2 === 0)
                                {
                                        $alerts[] = new Alert ('/etc/skel gekopieerd naar ' . $user->homedir, 'success');
                                }
                                else
                                {
                                        $alerts[] = new Alert ("Het voorbereiden van de home directory is mislukt ($exitStatus1,$exitStatus2). Voer de volgende commando's uit als root:" . PHP_EOL
                                                . '<pre>cp -R /etc/skel/ ' . $user->homedir . PHP_EOL
                                                . 'chown ' . $userInfo->username . ':' . $user->getGroup ()->name . ' ' . $user->homedir . ' -R</pre>', 'alert');

                                        $alerts[] = new Alert ('Output:<br /><pre>' . $output . '</pre>', 'secondary');
                                }
                        }
                        catch (Exception $ex)
                        {
                                $alerts[] = new Alert ("Het voorbereiden van de home directory is mislukt. Voer de volgende commando's uit als root:" . PHP_EOL
                                        . '<pre>cp -R /etc/skel/ ' . $user->homedir . PHP_EOL
                                        . 'chown ' . $userInfo->username . ':' . $user->getGroup ()->name . ' ' . $user->homedir . ' -R</pre>', 'alert');

                                $alerts[] = new Alert ('$o:<br /><pre>' . json_encode ($o) . '</pre>', 'secondary');
                        }

			$userLog = new UserLog ();
			$userLog->user_info_id = $userInfo->id;
			$userLog->nieuw = 1;
			$userLog->boekhouding = -1; // -1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd //
			$userLog->save ();

			$alerts[] = new Alert ('Opgeslagen in log als niet te factureren', 'success');
			
			DatabaseCredentials::forUserPrimary (Input::get ('username'), Input::get ('password'));
			
			DB::commit ();
			
			SinLog::log ('Gebruiker aangemaakt', NULL, $user, $userInfo, $userLog);
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (Exception $ex) // ->with ('ex', $ex) kan blijkbaar niet // Serialization of 'Closure' is not allowed //
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het aanmaken van de gebruiker is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
		}
	}
	
	public function edit ($user)
	{
		$userInfo = $user->userInfo;
		$groups = Group::all ();
		
		return View::make ('staff.user.user.edit', compact ('user', 'userInfo', 'groups'))->with ('alerts', array (new Alert ('Laat de wachtwoord-velden leeg indien u het huidige wachtwoord niet wenst te wijzigen.', 'info')));
	}
	
	public function update ($user)
	{
		$alerts = array ();
		
		try
		{
			DB::beginTransaction ();
			
			$strSecondaryGroups = implode (',', (array) Input::get ('groups'));

			$validator = Validator::make
			(
				array
				(
					'E-mailadres' => Input::get ('email'),
					'Voornaam' => Input::get ('fname'),
					'Achternaam' => Input::get ('lname'),
					'r-nummer' => Input::get ('rnummer'),
					'Shell' => Input::get ('shell'),
					'E-mail' => Input::get ('mailEnabled'),
					'Wachtwoord' => Input::get ('password'),
					'Wachtwoord (bevestiging)' => Input::get ('password_confirm'),
					'Primaire groep' => Input::get ('groupPrimary'),
					'Groepen' => Input::get ('groups')
				),
				array
				(
					'E-mailadres' => array ('required', 'email'),
					'Voornaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Achternaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'r-nummer' => '',	//array ('regex:/^(r|s|u)\d\d\d\d\d\d\d$/'),
					'Shell' => array ('required', 'in:/bin/bash,/usr/bin/fish,/usr/bin/zsh,/bin/false,/usr/bin/tmux'),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Wachtwoord' => array ('not_in:12345678,01234567,azertyui,qwertyui,aaaaaaaa,00000000,11111111', 'min:8', 'required_with:Wachtwoord (bevestiging)'),
					'Wachtwoord (bevestiging)' => array ('same:Wachtwoord', 'required_with:Wachtwoord'),
					'Primaire groep' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groepen' => array ('array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/' . $user->id . '/edit')->withInput ()->withErrors ($validator);

			if (! empty (Input::get ('password')))
			{
				$user->setPassword (Input::get ('password'));
				$user->lastchange = ceil (time () / 60 / 60 / 24);
				
				$alerts[] = new Alert ('Enkel het gebruikerswachtwoord is veranderd. Wachtwoorden van FTP-accounts e.d. zijn apart opgeslagen.', 'info');
			}
			$user->gcos = Input::get ('fname') . ' ' . Input::get ('lname') . ', ' . Input::get ('email');
			$user->gid = Input::get ('groupPrimary');
			$user->shell = Input::get ('shell');
			$user->mailEnabled = Input::get ('mailEnabled');

			$userInfo = $user->userInfo;
			$userInfo->fname = Input::get ('fname');
			$userInfo->lname = Input::get ('lname');
			$userInfo->email = Input::get ('email');
			$userInfo->schoolnr = Input::get ('rnummer');
			$userInfo->lastchange = ceil (time () / 60 / 60 / 24);

			$userInfo->save ();
			$user->save ();

			$alerts = array
			(
				new Alert ('Gebruiker bijgewerkt: ' . $userInfo->username, 'success')
			);

			$allGroups = Group::lists ('gid');
			$inputGroups = (array) Input::get ('groups');

			foreach ($allGroups as $gid) // Let op; Dit gaat niet over de primaire groep //
			{
				$userGroup = UserGroup::where ('uid', $user->uid)->where ('gid', $gid);

				if ($userGroup->count () < 1) // Geen  lid van groep //
				{
					if (in_array ($gid, $inputGroups)) // Wel aangevinkt in form //
					{
						$assoc = new UserGroup ();
						$assoc->uid = $user->uid;
						$assoc->gid = $gid;

						$assoc->save ();

						$group = Group::where ('gid', $gid)->first ();

						$alerts[] = new Alert ('Gebruiker ' . $userInfo->username . ' toegewezen aan groep: ' . ucfirst ($group->name), 'success');
					}
				}
				else // Reeds lid van groep //
				{
					if (! in_array ($gid, $inputGroups)) // Niet aangevinkt in form //
					{
						$userGroup->firstOrFail ()->delete ();

						$group = Group::where ('gid', $gid)->first ();

						$alerts[] = new Alert ('Gebruiker ' . $userInfo->username . ' verwijderd uit groep: ' . ucfirst ($group->name), 'success');
					}
				}
			}
			
			DB::commit ();
			
			SinLog::log ('Gebruiker bijgewerkt', NULL, $user, $userInfo);
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (Exception $ex)
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het bijwerken van de gebruiker is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
		}
	}
	
	public function remove ($user) // UserInfo blijft behouden voor UserLog //
	{
		$alerts = array ();
		
		if (Input::get ('confirm') === 'pizza') // Voor iets ernstig als het verwijderen van een gebruiker best niet enkel vertrouwen op Javascript confirm () //
		{
			try
			{
				DB::beginTransaction ();

				$alerts = array ();
				$userInfo = $user->userInfo;

				/*
				 * Andere dingen verwijderen gebeurt normaalgesproken al via de CASCADE DELETE in de database.
				 * Dit gebeurt echter niet via de de ->remove () method.
				 * Ik ga deze dus manueel verwijderen, want sommige entities (zoals de vHosts) hebben custom code
				 * in hun ->remove () method zitten die best uitgevoerd wordt bij verwijdering.
				 */

				foreach (FtpUserVirtual::where ('uid', $user->uid)->get () as $ftp)
				{
					$ftp->delete ();
					$alerts[] = new Alert ('FTP-account verwijderd: ' . $ftp->user, 'success');
				}

				foreach (ApacheVhostVirtual::where ('uid', $user->uid)->get () as $vhost)
				{
					$vhost->delete ();
					$alerts[] = new Alert ('vHost verwijderd: ' . $vhost->servername, 'success');
				}

				foreach (MailUserVirtual::where ('uid', $user->uid)->get () as $mUser)
				{
					$mUser->delete ();
					$alerts[] = new Alert ('E-mailaccount verwijderd: ' . $mUser->email, 'success');
				}

				foreach (MailForwardingVirtual::where ('uid', $user->uid)->get () as $mFwd)
				{
					$mFwd->delete ();
					$alerts[] = new Alert ('Doorstuuradres verwijderd: ' . $mFwd->source, 'success');
				}

				foreach (MailDomainVirtual::where ('uid', $user->uid)->get () as $domain)
				{
					$domain->delete ();
					$alerts[] = new Alert ('E-maildomein verwijderd: ' . $domain->domain, 'success');
				}

				$user->delete ();
				//$userInfo->delete ();

				$alerts[] = new Alert ('Gebruiker verwijderd: ' . $userInfo->username, 'success');

				DB::commit ();
				
				SinLog::log ('Gebruiker verwijderd', NULL, $user, $userInfo);

				return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
			}
			catch (Exception $ex)
			{
				DB::rollback ();

				return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het verwijderen van de gebruiker is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
			}
		}
		else
		{
			die ('Een gebruiker verwijderen? Dat is toch wel vrij drastisch. Deze (ietwat ruw geïmplementeerde) beveiliging is er om er voor te zorgen dat een gebruiker niet plots zijn account kwijt is als iemand zijn muisvinger even slipt en de Javascript `alert ()` dialog er geen zin in heeft. Indien je toch wil doorgaan, zet `?confirm=pizza` achter de URL.');
		}
	}

	public function login ($user)
	{
		$userInfo = $user->userInfo;
		
		SinLog::log ('Ingelogd als gebruiker', NULL, $user); // Hier moet de log vóór de effectieve actie gebeuren, anders klopt de user_id in de log entry niet //
		
		Auth::login ($user);
		
		return Redirect::to ('/user/start')->with ('alerts', array (new Alert ('Ingelogd als gebruiker: ' . $userInfo->username . ' (' . $userInfo->fname . ' ' . $userInfo->lname . ')')));
	}
	
	public function getExpire ($user)
	{
		$validUntilUnix = $user->expire * 24 * 60 * 60;
		$validUntilDate = date ('D j F Y', $validUntilUnix);
		$validUntilShortDate = date ('d-m-Y', $validUntilUnix);
		$stillValidUnix = $validUntilUnix - time ();
		$stillValidDate = (int) ($stillValidUnix / 60 / 60 / 24) . ' dagen';
		
		$septemberYet = (idate ('n') >= 9);
		
		$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);
		$next1OctUnix = strtotime ('Oct 1,' . $nextYear);
		$next1OctDate = date ('D j F Y', $next1OctUnix);
		
		$nowUnix = time ();
		$nowDate = date ('D j F Y', $nowUnix);
		
		$expires = array
		(
			$validUntilUnix => 'Huidig: ' . $validUntilDate,
			$next1OctUnix => 'Volgende 1 oktober: ' . $next1OctDate,
			$nowUnix => 'Nu: ' . $nowDate,
			-1 * 24 * 60 * 60 => 'Vervalt nooit'
		);
		
		if ($user->expire === -1)
		{
			$validUntilDate = 'Altijd';
			$validUntilUnix = 'Niet van toepassing';
			$validUntilShortDate = '';
			$stillValidDate = '&infin;';
			$stillValidUnix = '';
		}
		
		$alerts = array
		(
			new Alert ('Wanneer de vervaldatum door een medewerker wordt gewijzigd zal de gebruiker hiervoor niet gefactureerd worden.', 'warning')
		);
		
		return View::make ('staff.user.user.expire', compact ('user', 'validUntilUnix', 'validUntilDate', 'stillValidUnix', 'stillValidDate', 'validUntilShortDate', 'expires', 'alerts'));
	}
	
	public function expire ($user)
	{
		$validator = Validator::make
		(
			array
			(
				'Vervaldatum' => Input::get ('expire')
			),
			array
			(
				'Vervaldatum' => array ('integer')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/user/user/' . $user->id . '/expire')->withInput ()->withErrors ($validator);
		
		$newExpireDays;
		$newExpireDate;
		
		if (Input::get ('expire') > 0)
		{
			$newExpireDays = ceil (Input::get ('expire') / 60 / 60 / 24);
			$newExpireDate = date ('D j F Y', Input::get ('expire'));
		}
		else
		{
			$newExpireDays = -1;
			$newExpireDate = 'Vervalt nooit';
		}
		
		$user->expire = $newExpireDays;
		$user->save ();
		
		/*
		 * Alle vHosts voor gebruiker ophalen en en terug opslaan aangezien
		 * in ApacheVhostVirtual->save () de check gebeurt of de gebruiker expired
		 * en of dus de expired document root moet worden ingesteld of de echte document root.
		 */
		foreach ($user->vhost as $vhost)
			$vhost->save ();
		
		SinLog::log ('Vervaltdatum bijgewerkt', NULL, $user);
		
		return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('Vervaldatum van ' . $user->userInfo->username . ' (' . $user->userInfo->getFullName () . ') ingesteld: ' . $newExpireDate)));
	}
	
	public function getValidate ($userInfo)
	{
		$uid = User::max ('uid') + 1;
		$groups = Group::all ();
		
		return View::make ('staff.user.user.validate', compact ('userInfo', 'uid', 'groups'));
	}
	
	public function validate ($userInfo)
	{
		$alerts = array ();
		
		try
		{
			if ($userInfo->validated == 1)
				return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('Gebruiker is al gevalideerd', 'alert')));
			
			DB::beginTransaction ();
			
			$uid = User::max ('uid') + 1;

			$inputHomedir = rtrim (Input::get ('homedir'), '/');

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

			$strSecondaryGroups = implode (',', (array) Input::get ('groups'));

			$validator = Validator::make
			(
				array
				(
					'UID' => Input::get ('uid'),
					'Gebruikersnaam' => Input::get ('username'),
					'Home directory' => $inputHomedir,
					'E-mailadres' => Input::get ('email'),
					'Voornaam' => Input::get ('fname'),
					'Achternaam' => Input::get ('lname'),
					'r-nummer' => Input::get ('rnummer'),
					'Shell' => Input::get ('shell'),
					'E-mail' => Input::get ('mailEnabled'),
					'Primaire groep' => Input::get ('groupPrimary'),
					'Groepen' => Input::get ('groups')
				),
				array
				(
					'UID' => array ('required', 'unique:user,uid', 'integer', 'min:' . $uid, 'max:' . $uid),
					'Gebruikersnaam' => array ('required', 'alpha_num', 'min:4', 'max:14', 'not_in:' . $strReservedUsers),
					'Home directory' => array ('unique:user,homedir', 'regex:/^\/home\/[^\/]+\/[a-z0-9]\/[a-z0-9]+$/'),
					'E-mailadres' => array ('required', 'email'),
					'Voornaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'Achternaam' => array ('required', 'regex:/^[^\,\;\\\]+$/'),
					'r-nummer' => array ('required'/*, 'regex:/^r\d\d\d\d\d\d\d$/'*/),
					'Shell' => array ('required', 'in:/bin/bash,/bin/fish,/bin/zsh,/bin/false,/usr/bin/tmux'),
					'E-mail' => array ('required', 'in:-1,0,1'),
					'Primaire groep' => array ('required', 'exists:group,gid', 'not_in:' . $strSecondaryGroups),
					'Groepen' => array ('array', 'exists:group,gid')
				)
			);

			if ($validator->fails ())
				return Redirect::to ('/staff/user/user/' . $userInfo->id . '/validate')->withInput ()->withErrors ($validator);

			$septemberYet = (idate ('n') >= 9);
			$nextYear = idate ('y', time ()) + ($septemberYet ? 1 : 0);
			$next1OctUnix = strtotime ('Oct 1,' . $nextYear);
			$next1OctDays = ceil ($next1OctUnix / 60 / 60 / 24);

			$etc = unserialize ($userInfo->etc);

			$user = new User ();
			$user->uid = Input::get ('uid');
			$user->crypt = $etc['password'];
			$user->gcos = Input::get ('fname') . ' ' . Input::get ('lname') . ', ' . Input::get ('email');
			$user->gid = Input::get ('groupPrimary');
			$user->homedir = $inputHomedir;
			$user->shell = Input::get ('shell');
			$user->lastchange = time () / 60 / 60 / 24;
			$user->mailEnabled = Input::get ('mailEnabled');
			$user->expire = $next1OctDays;

			$userInfo->username = Input::get ('username');
			$userInfo->fname = Input::get ('fname');
			$userInfo->lname = Input::get ('lname');
			$userInfo->email = Input::get ('email');
			$userInfo->schoolnr = Input::get ('rnummer');
			$userInfo->lastchange = time () / 60 / 60 / 24;
			$userInfo->etc = null;
			$userInfo->validated = 1;

			$userInfo->save ();
			$user->user_info_id = $userInfo->id;
			$user->save ();

			$alerts = array
			(
				new Alert ('Gebruiker aangemaakt: ' . Input::get ('username'), 'success')
			);

			foreach ((array) Input::get ('groups') as $gid)
			{
				$assoc = new UserGroup ();
				$assoc->uid = $user->uid;
				$assoc->gid = $gid;

				$assoc->save ();

				$group = Group::where ('gid', $gid)->first ();

				$alerts[] = new Alert ('Gebruiker ' . $userInfo->username . ' toegewezen aan groep: ' . ucfirst ($group->name), 'success');
			}

			$vhost = new ApacheVhostVirtual (); // User's default vHost //
			$vhost->uid = $user->uid;
			$vhost->docroot = $user->homedir . '/public_html';
			$vhost->servername = $userInfo->username . '.sinners.be';
			$vhost->serveralias = 'www.' . $userInfo->username . '.sinners.be';
			$vhost->serveradmin = $userInfo->username . '@sinners.be';
			$vhost->cgi = 1;
			$vhost->ssl = 0;
			$vhost->locked = 1; // Enkel bewerkbaar door staff //
			$vhost->save ();

			$alerts[] = new Alert ('vHost toegevoegd: ' . $vhost->servername, 'success');

			$ftp = new FtpUserVirtual (); // User's default FTP account //
			$ftp->user = $userInfo->username;
			$ftp->uid = $user->uid;
			$ftp->passwd = $user->crypt;
			$ftp->dir = $user->homedir;
			$ftp->locked = 1; // Enkel bewerkbaar door staff //
			$ftp->save ();

			$alerts[] = new Alert ('FTP-account toegevoegd: ' . $ftp->user, 'success');

			$userLog = new UserLog ();
			$userLog->user_info_id = $userInfo->id;
			$userLog->nieuw = 1;
			$userLog->boekhouding = 0; // -1 = Niet te factureren // 0 = Nog te factureren // 1 = Gefactureerd //
			$userLog->save ();

			$alerts[] = new Alert ('Opgeslagen in log als nog te factureren', 'success');

			/*exec ('sudo cp -R /etc/skel/ ' . escapeshellarg ($user->homedir) . '2>&1', $output, $exitStatus1);
			exec ('sudo chown ' . $userInfo->username . ':' . $user->getGroup ()->name . ' ' . $user->homedir . ' -R 2>&1', $output, $exitStatus2);*/
			
			$cmd = 'php /home/users/s/sin/scripts/prepareUserHomedir.php ' . escapeshellarg ($userInfo->username) . ' ' . escapeshellarg ($user->homedir) . ' ' . escapeshellarg ($user->getGroup ()->name);
			exec ($cmd, $o);
			
			try
			{
				$exitStatus1 = $o[0];
				$exitStatus2 = $o[1];
				$output = $o[2];
				
				if ($exitStatus1 === 0 && $exitStatus2 === 0)
                	        {
        	                        $alerts[] = new Alert ('/etc/skel gekopieerd naar ' . $user->homedir, 'success');
	                        }
                        	else
                	        {
        	                        $alerts[] = new Alert ("Het voorbereiden van de home directory is mislukt ($exitStatus1,$exitStatus2). Voer de volgende commando's uit als root:" . PHP_EOL
	                                        . '<pre>cp -R /etc/skel/ ' . $user->homedir . PHP_EOL
                                        	. 'chown ' . $userInfo->username . ':' . $user->getGroup ()->name . ' ' . $user->homedir . ' -R</pre>', 'alert');
                                	
                                	$alerts[] = new Alert ('Output:<br /><pre>' . $output . '</pre>', 'secondary');
					$alerts[] = new Alert ('Commando uitgevoerd door SINControl:<br /><pre>' . $cmd . '</pre>', 'secondary');
                        	}
			}
                        catch (Exception $ex)
                        {
                                $alerts[] = new Alert ("Het voorbereiden van de home directory is mislukt. Voer de volgende commando's uit als root:" . PHP_EOL
                                        . '<pre>cp -R /etc/skel/ ' . $user->homedir . PHP_EOL
                                        . 'chown ' . $userInfo->username . ':' . $user->getGroup ()->name . ' ' . $user->homedir . ' -R</pre>', 'alert');

                                $alerts[] = new Alert ('$o:<br /><pre>' . json_encode ($o) . '</pre>', 'secondary');
                        }

			DatabaseCredentials::forUserPrimary_hash ($userInfo->username, $etc['mysql_hash']);
			
			DB::commit ();
			
			Mail::send ('email.user.activated', compact ('userInfo'), function ($msg) use ($userInfo)
				{
					$msg->to ($userInfo->email, $userInfo->getFullName ())->subject ('Uw SIN-account is geactiveerd');
				}
			);
			
			SinLog::log ('Gebruiker gevalideerd', NULL, $user, $userInfo);
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (Exception $ex)
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het bijwerken van de gebruiker is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
		}
	}
	
	public function reject ($userInfo)
	{
		$userInfo->delete ();
		
		SinLog::log ('Gebruikersregistratie geweigerd', NULL, $userInfo);
		
		return Redirect::to ('/staff/user/user')->with ('alerts', array (new Alert ('Validatie geweigerd: ' . $userInfo->username . PHP_EOL . '</br />Gebruikersinformatie verwijderd.<br />Let op: Er is <strong>geen</strong> geautomatiseerde e-mail verstuurd naar de gebruiker in kwestie. Gelieve (indien het om een gebruiker ging, en geen bot o.i.d.) zelf even een e-mail naar de gebruiker in kwestie te sturen en er ook duidelijk bij te zeggen <strong>waarom</strong> zijn registratie geweigerd is.')));
	}
	
	public function more ($user)
	{
		$userInfo = $user->userInfo;
		$groups = Group::all ();
		
		try
		{
			$userMailEnabledMap = array
			(
				'0' => 'Uit',
				'1' => 'Aan',
				'-1' => 'Blokkeren'
			);
			$userMailEnabledPretty = $userMailEnabledMap[$user->mailEnabled] . ' (' . $user->mailEnabled . ')';
		}
		catch (Exception $ex)
		{
			$userMailEnabledPretty = $user->mailEnabled;
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
				$cryptAlgorithmPretty = 'Onbekend';
		}
		$cryptAlgorithmPretty .= ' ($' . $cryptAlgorithm . '$)';
		
		return View::make ('staff.user.user.more', compact ('user', 'userInfo', 'groups', 'userMailEnabledPretty', 'cryptAlgorithmPretty'));
	}
	
	public function generateLoginToken ($user)
	{
		$userInfo = $user->userInfo;
		
		$userInfo->generateLoginToken ();
		$userInfo->save ();
		
		SinLog::log ('Eenmalige login token gegenereerd', NULL, $user, $userInfo);
		
		return Redirect::to ('staff/user/user/' . $user->id . '/more')->with ('alerts', array (new Alert ('Eenmalige login token gegenereerd. Deze kan doorgegeven worden aan de gebruiker in kwestie zodat deze zelf via <em>Gebruiker</em> -> <em>Gegevens wijzigen</em> een nieuw wachtwoord kan instellen voor zijn/haar account.<br />Deze link zal automatisch vervallen wanneer deze gebruikt wordt.', 'info')));
	}
}
