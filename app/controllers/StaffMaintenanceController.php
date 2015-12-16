<?php

class StaffMaintenanceController extends BaseController
{
	public function generateVHosts ()
	{
		try
		{
			$vhosts = ApacheVhostVirtual::all ();
			$alerts = array ();

			foreach ($vhosts as $vhost)
			{
				$vhost->save ();

				$alerts[] = new Alert ('vHost aangemaakt: ' . $vhost->servername, 'success');
			}
			
			$user = Auth::user ();
			$userInfo = $user->userInfo;
			
			SinLog::log ('vHosts opnieuw gegenereerd');

			return View::make ('user.start', compact ('alerts', 'user', 'userInfo'));
		}
		catch (Exception $ex)
		{
			return Redirect::to ('/error')->with ('ex', new SinException ($ex));
		}
	}
	
	public function saveAllVHosts () // Er... waarom bestaat deze functie? Tenzij ik blind ben vanavond doet die exact hetzelfde als generateVHosts ()? //
	{
		try
		{
			$vhosts = ApacheVhostVirtual::all ();
			$alerts = array ();
			
			foreach ($vhosts as $vhost)
			{
				$vhost->save ();
				
				$alerts[] = new Alert ('vHost opnieuw opgeslagen: ' . $vhost->servername, 'success');
			}
			
			$user = Auth::user ();
			$userInfo = $user->userInfo;
			
			SinLog::log ('vHosts opnieuw opgeslagen');
			
			return View::make ('user.start', compact ('alerts', 'user', 'userInfo'));
		}
		catch (Exception $ex)
		{
			return Redirect::to ('/error')->with ('ex', new SinException ($ex));
		}
	}
	
	public function generateServiceData ()
	{
		$alerts = array ();
		
		try
		{
			DB::beginTransaction ();
			
			$users = User::all ();
			
			foreach ($users as $user)
			{
				$userInfo = $user->userInfo;
				
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
			}
			
			DB::commit ();
			
			SinLog::log ('Service data gegenereerd');
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (Exception $ex) // ->with ('ex', $ex) kan blijkbaar niet // Serialization of 'Closure' is not allowed //
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het aanmaken van de gebruikersdata is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
		}
	}
	
	public function systemCheck ()
	{
		try
		{
			DB::beginTransaction ();
			
			$alerts = array ();
			
			$users = User::all ();
			foreach ($users as $user)
			{
				if
				(
					empty ($user->id)
					|| empty ($user->uid)
					|| empty ($user->user_info_id)
					|| empty ($user->crypt)
					|| empty ($user->gcos)
					|| empty ($user->gid)
					|| empty ($user->homedir)
					|| empty ($user->shell)
					|| empty ($user->lastchange)
					|| empty ($user->expire)
				)
				{
					$alerts[] = new Alert ('Gebruiker heeft ontbrekende velden: ' . $user->id, 'warning');
				}
				
				$userInfo = $user->userInfo;
				
				if (empty ($userInfo))
				{
					$alerts[] = new Alert ('Gebruiker heeft geen geassociëerde rij in de <kbd>user_info</kbd>-tabel: ' . $user->id, 'alert');
					
					break;
				}
				
				/*
				$nUserLogs = UserLog::where ('user_info_id', $userInfo->id)->count ();
				if ($nUserLogs < 1)
					$alerts[] = new Alert ('Gebruiker heeft geen gelogde facturaties: ' . $user->id, 'secondary');
				*/
				
				if (! is_dir ($user->homedir))
					$alerts[] = new Alert ('Gebruiker bestaat maar zijn/haar home directory niet: ' . $user->id, 'alert');
				
				if (is_dir ($user->homedir)){
					if (fileowner ($user->homedir) != $user->uid)
						$alerts[] = new Alert ('Gebruiker bestaat maar zijn/haar home directory heeft niet de juiste eigenaar: ' . $user->id, 'alert');
				}
				
				
			}
			
			$userInfos = UserInfo::all ();
			foreach ($userInfos as $userInfo)
			{
				if
				(
					empty ($userInfo->id)
					|| empty ($userInfo->username)
					|| empty ($userInfo->fname)
					|| empty ($userInfo->lname)
					|| empty ($userInfo->email)
				)
				{
					$alerts[] = new Alert ('Gebruikersinformatie heeft ontbrekende velden: ' . $userInfo->id, 'warning');
				}
				
				if (empty ($userInfo->schoolnr)) // Komt vaak voor, dus minder kritieke melding //
					$alerts[] = new Alert ('Gebruikersinformatie mist r-nummer: ' . $userInfo->id, 'secondary');
				
				if ($userInfo->validated == 1 && ( !$userInfo->userExists ()))
					$alerts[] = new Alert ('Gebruikersinformatie zegt dat gebruiker gevalideerd is, maar er is geen rij aanwezig in de <kbd>user</kbd>-tabel voor de gebruiker in kwestie: ' . $userInfo->id, 'alert');
			}
			
			$groups = Group::all ();
			foreach ($groups as $group)
			{
				if
				(
					empty ($group->id)
					|| empty ($group->name)
					|| empty ($group->gid)
					|| $group->passwd == 'x'
				)
				{
					$alerts[] = new Alert ('Gebruikersgroep heeft ontbrekende velden: ' . $group->id, 'warning');
				}
			}
			
			$ftps = FtpUserVirtual::all ();
			foreach ($ftps as $ftp)
			{
				if
				(
					empty ($ftp->id)
					|| empty ($ftp->uid)
					|| empty ($ftp->user)
					|| empty ($ftp->passwd)
					|| empty ($ftp->dir)
				)
				{
					$alerts[] = new Alert ('FTP-account heeft ontbrekende velden: ' . $ftp->id, 'warning');
				}
			}
			
			$mailDomains = MailDomainVirtual::all ();
			foreach ($mailDomains as $domain)
			{
				if
				(
					empty ($domain->id)
					|| empty ($domain->uid)
					|| empty ($domain->domain)
				)
				{
					$alerts[] = new Alert ('E-maildomein heeft ontbrekende velden: ' . $domain->id, 'warning');
				}
			}
			
			$mailUsers = MailUserVirtual::all ();
			foreach ($mailUsers as $mUser)
			{
				if
				(
					empty ($mUser->id)
					|| empty ($mUser->uid)
					|| empty ($mUser->email)
				    	|| empty ($mUser->password)
				)
				{
					$alerts[] = new Alert ('E-mailgebruiker heeft ontbrekende velden: ' . $mUser->id, 'warning');
				}
			}
			
			$mailFwds = MailForwardingVirtual::all ();
			foreach ($mailFwds as $mFwd)
			{
				if
				(
					empty ($mFwd->id)
					|| empty ($mFwd->uid)
					|| empty ($mFwd->source)
				    	|| empty ($mFwd->destination)
				)
				{
					$alerts[] = new Alert ('Doorstuurdadres heeft ontbrekende velden: ' . $mFwd->id, 'warning');
				}
			}
			
			$pages = Page::all ();
			foreach ($pages as $page)
			{
				if
				(
					empty ($page->id)
					|| empty ($page->name)
					|| empty ($page->title)
				    	|| empty ($page->content)
				)
				{
					$alerts[] = new Alert ('Pagina heeft ontbrekende velden: ' . $page->id, 'warning');
				}
			}
			
			$systemTasks = SystemTask::all ();
			foreach ($systemTasks as $task)
			{
				if
				(
					empty ($task->id)
					|| empty ($task->type)
				)
				{
					$alerts[] = new Alert ('Pagina heeft ontbrekende velden: ' . $page->id, 'warning');
				}
				
				if ($task->started == 1 && (time () + 5 > $task->start) && empty ($task->exitcode))
					$alerts[] = new Alert ('Systeemtaak zou gestart moeten zijn maar heeft geen exit code: ' . $task->id, 'warning');
			}
			
			DB::commit ();
			
			$alerts[] = new Alert ('Systeemcheck succesvol beëindigd', 'success');
			
			SinLog::log ('Systeemcheck uitgevoerd', NULL, $alerts);

			return Redirect::to ('/user/start')->with ('alerts', $alerts);
		}
		catch (Exception $ex)
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Systeemcheck mislukt. Als dat ondertussen al fatsoenlijk werkt zouden alle databasetransacties moeten zijn teruggerold.', 'alert')));
		}
	}
}