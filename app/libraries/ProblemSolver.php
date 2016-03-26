<?php

class ProblemSolver
{
	private $user;
	
	function __construct ($user)
	{
		$this->user = $user;
	}
	
	public function run ($fix = true)
	{
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
			'USER_NOT_VALIDATED' => array
			(
				'name' => 'USER_NOT_VALIDATED',
				'message' => 'Gebruiker is nog niet gevalideerd'
			),
			'RETARDED_MEDEWERKER' => array
			(
				'name' => 'RETARDED_MEDEWERKER',
				'message' => 'Eén of andere retarded medewerker heeft weer gebruikers zitten valideren zonder de documentatie te lezen.'
			),
		);
		$problems = array ();
		
		// vHost-gerelateerde problemen //
		if ($this->user->hasExpired ())
		{
			$problems[] = array ('USER_EXPIRED');
		}
		else if (! $this->user->userInfo->validated)
		{
			$problems[] = array ('USER_NOT_VALIDATED');
		}
		else if (! file_exists ($this->user->homedir))
		{
			$problems[] = array ('RETARDED_MEDEWERKER');
		}
		else
		{
			foreach ($this->user->vhost as $vhost)
			{
				if (! file_exists ($vhost->path ()))
				{
					if ($fix)
						$vhost->save (); // vHost file zou geschreven moeten worden //
					
					$problems[] = array ('VHOST_FILE_ABSENT', 'vHost-configuratiebestand weggeschreven', $vhost);
				}
				else if (preg_match ('#\s*DocumentRoot\s+expired#i', file_get_contents ($vhost->path ())))
				{
					if ($fix)
						$vhost->save (); // vHost file zou opnieuw geschreven moeten worden //
					
					$problems[] = array ('VHOST_NOT_RENEWED', 'vHost-configuratiebestand herschreven', $vhost);
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
						if ($fix)
							$status = $this->createDirectory ($vhost->docroot, $this->user, '711');
						
						$problems[] = array ('DOCROOT_ABSENT', 'Document root aangemaakt' . (isset ($status) && $status['exitcode'] > 0 ? ' (mogelijk mislukt)' : ''), $vhost); // Document root van de vHost lijkt niet te bestaan; Automatisch proberen te fixen kan riskant zijn //
					}
				}
				
				if (! (file_exists ($this->user->homedir . '/logs') && is_dir ($this->user->homedir . '/logs')))
				{
					if ($fix)
						$status = $this->createDirectory ($this->user->homedir . '/logs', $this->user, '711');
					
					$problems[] = array ('LOGS_FOLDER_ABSENT', 'Map met logbestanden aangemaakt' . (isset ($status) && $status['exitcode'] > 0 ? ' (mogelijk mislukt)' : ''), $this->user); // Weer zo ene die zijne logs folder verwijderd heeft... //
				}
			}
		}
		
		$data = array ();
		foreach ($problems as $info)
		{
			if (! isset ($info[1]) || ! $fix)
				$info[1] = NULL;
			if (! isset ($info[2]))
				$info[2] = NULL;
			
			$data[] = array_merge
			(
				array
				(
					'fix' => $info[1],
					'object' => (string) $info[2]
				),
				$knownProblems[$info[0]]
			);
		}
		
		SinLog::log ('ProblemSolver uitgevoerd' . (! $fix ? ' (dry run)' : ''), NULL, $data);
		
		return $data;
	}
	
	private function createDirectory ($directory, User $owner = NULL, $permissions = NULL)
	{
		$output = array ();
		
		$cmd1 = 'mkdir -p ' . escapeshellarg ($directory) . ' 2>&1';
		exec ($cmd1, $output, $exitStatus1);
		
		if ($owner !== NULL)
		{
			$cmd2 = 'chown ' . escapeshellarg ($owner->userInfo->username) . ':' . escapeshellarg ($owner->primaryGroup->name) . ' ' . escapeshellarg ($directory) . ' 2>&1';
			exec ($cmd2, $output, $exitStatus2);
		}
		
		if ($permissions !== NULL)
		{
			if (! is_string ($permissions))
				throw new Exception ('Permissions should be passed at string, just to be safe...');
			
			$cmd3 = 'chmod ' . escapeshellarg ($permissions) . ' ' . escapeshellarg ($directory) . ' 2>&1';
			exec ($cmd3, $output, $exitStatus3);
		}
		
		return array
		(
			'exitcode' => max ($exitStatus1, $exitStatus2, $exitStatus3),
			'command' => array ($cmd1, $cmd2, $cmd3),
			'output' => implode (PHP_EOL, $output)
		);
	}
}