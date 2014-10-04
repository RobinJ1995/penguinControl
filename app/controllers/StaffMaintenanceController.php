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
			$userInfo = $user->getUserInfo ();

			return View::make ('user.start', compact ('alerts', 'user', 'userInfo'));
		}
		catch (Exception $ex)
		{
			return Redirect::to ('/error')->with ('ex', new SinException ($ex));
		}
	}
	
	public function saveAllVHosts ()
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
			$userInfo = $user->getUserInfo ();
			
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
				$userInfo = $user->getUserInfo ();
				
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
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (Exception $ex) // ->with ('ex', $ex) kan blijkbaar niet // Serialization of 'Closure' is not allowed //
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new SinException ($ex))->with ('alerts', array (new Alert ('Het aanmaken van de gebruikersdata is mislukt. Alle databasetransacties zijn teruggerold.', 'alert')));
		}
	}
}