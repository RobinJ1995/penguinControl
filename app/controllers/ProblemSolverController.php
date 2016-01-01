<?php

class ProblemSolverController extends BaseController
{
	public function start ()
	{
		$userId = Auth::user ()->id;
		
		return View::make ('problem-solver.start', compact ('userId'));
	}
	
	public function scan ()
	{
		$user = User::find (Input::get ('userId'));
		
		$knownProblems = array
		(
			'VHOST_FILE_ABSENT' => array
			(
				'name' => 'VHOST_FILE_ABSENT',
				'message' => 'vHost-configuratiebestand bestaat niet'
			),
			'VHOST_NOT_RENEWED' => array
			(
				'name' => 'VHOST_NOT_RENEWED',
				'message' => 'vHost-configuratiebestand niet bijgewerkt bij accountverlenging'
			),
			'HOMEDIR_STORAGE_UNAVAILABLE' => array
			(
				'name' => 'HOMEDIR_STORAGE_UNAVAILABLE',
				'message' => 'Opslagapparaat waar gebruikersdata staat opgeslagen lijkt mogelijk onbereikbaar. Dit kan duiden op een technische storing in het systeem.'
			),
			'DOCROOT_ABSENT' => array
			(
				'name' => 'DOCROOT_ABSENT',
				'message' => "vHost's opgegeven document root bestaat niet"
			),
			'LOGS_FOLDER_ABSENT' => array
			(
				'name' => 'LOGS_FOLDER_ABSENT',
				'message' => 'Map met logbestanden bestaat niet'
			),
			'USER_EXPIRED' => array
			(
				'name' => 'USER_EXPIRED',
				'message' => 'Gebruiker is vervallen'
			),
		);
		$problems = array ();
		
		// vHost-gerelateerde problemen //
		if (! $user->hasExpired ())
		{
			foreach ($user->vhost as $vhost)
			{
				if (! file_exists ($vhost->path ()))
				{
					$problems[] = array ('VHOST_FILE_ABSENT', 'vHost-configuratiebestand weggeschreven', $vhost->filename ());
					
					$vhost->save (); // vHost file zou geschreven moeten worden //
				}
				else if (preg_match ('#\s*DocumentRoot\s+expired#i', file_get_contents ($vhost->path ())))
				{
					$problems[] = array ('VHOST_NOT_RENEWED', 'vHost-configuratiebestand herschreven', $vhost->filename ());
					
					$vhost->save (); // vHost file zou opnieuw geschreven moeten worden //
				}
				
				if (! (file_exists ($vhost->docroot) && is_dir ($vhost->docroot)))
				{
					$sinUser = UserInfo::where ('username', 'sin')->firstOrFail ()->user;
					if (! (file_exists ($sinUser->homedir) && is_dir ($sinUser->homedir)))
					{
						$problems[] = array ('HOMEDIR_STORAGE_UNAVAILABLE'); // Problemen met de NAS? De home directories lijken niet beschikbaar te zijn... //
					}
					else
					{
						$problems[] = array ('DOCROOT_ABSENT'); // Document root van de vHost lijkt niet te bestaan; Automatisch proberen te fixen kan riskant zijn //
					}
				}
				
				if (! (file_exists ($user->homedir . '/logs') && is_dir ($user->homedir . '/logs')))
				{
					$problems[] = array ('LOGS_FOLDER_ABSENT'); // Weer zo ene die zijne logs folder verwijderd heeft... Geen root-rechten -> Kan voorlopig nog niet door SINControl opgelost worden. //
				}
			}
		}
		else
		{
			$problems[] = array ('USER_EXPIRED');
		}
		
		$data = array ();
		foreach ($problems as $info)
		{
			if (! isset ($info[1]))
				$info[1] = NULL;
			if (! isset ($info[2]))
				$info[2] = NULL;
			
			$data[] = array_merge
			(
				array
				(
					'fix' => $info[1],
					'details' => $info[2]
				),
				$knownProblems[$info[0]]
			);
		}
		
		return Response::json ($data);
	}
}