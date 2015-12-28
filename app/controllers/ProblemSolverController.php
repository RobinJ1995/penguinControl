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
					$problems[] = $knownProblems['VHOST_FILE_ABSENT'];
					
					$vhost->save (); // vHost file zou geschreven moeten worden //
				}
				else if (preg_match ('#\s*DocumentRoot\s+expired#i', file_get_contents ($vhost->path ())))
				{
					$problems[] = $knownProblems['VHOST_NOT_RENEWED'];
					
					$vhost->save (); // vHost file zou opnieuw geschreven moeten worden //
				}
				
				if (! (file_exists ($vhost->docroot) && is_dir ($vhost->docroot)))
				{
					$sinUser = UserInfo::where ('username', 'sin')->firstOrFail ()->user;
					if (! (file_exists ($sinUser->homedir) && is_dir ($sinUser->homedir)))
					{
						$problems[] = $knownProblems['HOMEDIR_STORAGE_UNAVAILABLE']; // Problemen met de NAS? De home directories lijken niet beschikbaar te zijn... //
					}
					else
					{
						$problems[] = $knownProblems['DOCROOT_ABSENT']; // Document root van de vHost lijkt niet te bestaan; Automatisch proberen te fixen kan riskant zijn //
					}
				}
				
				if (! (file_exists ($user->homedir . '/logs') && is_dir ($user->homedir . '/logs')))
				{
					$problems[] = $knownProblems['LOGS_FOLDER_ABSENT']; // Weer zo ene die zijne logs folder verwijderd heeft... Geen root-rechten -> Kan voorlopig nog niet door SINControl opgelost worden. //
				}
			}
		}
		else
		{
			$problems[] = $knownProblems['USER_EXPIRED'];
		}
		
		return Response::json ($problems);
	}
}