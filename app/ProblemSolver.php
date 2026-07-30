<?php

namespace App;

use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

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
				'message' => 'vHost configuration file does not exist'
			),
			'VHOST_NOT_RENEWED' => array
			(
				'name' => 'VHOST_NOT_RENEWED',
				'message' => 'vHost configuration file was not updated on account renewal'
			),
			'HOMEDIR_STORAGE_UNAVAILABLE' => array
			(
				'name' => 'HOMEDIR_STORAGE_UNAVAILABLE',
				'message' => 'Home directory storage device is not available'
			),
			'DOCROOT_ABSENT' => array
			(
				'name' => 'DOCROOT_ABSENT',
				'message' => 'vHost\'s document root does not exist'
			),
			'LOGS_FOLDER_ABSENT' => array
			(
				'name' => 'LOGS_FOLDER_ABSENT',
				'message' => 'Log folder does not exist'
			),
			'USER_EXPIRED' => array
			(
				'name' => 'USER_EXPIRED',
				'message' => 'User has expired'
			),
			'USER_NOT_VALIDATED' => array
			(
				'name' => 'USER_NOT_VALIDATED',
				'message' => 'User has not yet been activated'
			),
			'HOMEDIR_ABSENT' => array
			(
				'name' => 'HOMEDIR_ABSENT',
				'message' => 'A user was validated without following the documentation: the home directory is missing.'
			),
		);
		$problems = array ();
		
		// vHost-related problems //
		if ($this->user->hasExpired ())
		{
			$problems[] = array ('USER_EXPIRED');
		}
		else if (! $this->user->userInfo->validated)
		{
			$problems[] = array ('USER_NOT_VALIDATED');
		}
		else if (! file_exists ($this->user->homedir) && ! App::environment ('local')) // Otherwise this would trigger constantly in a local dev environment //
		{
			$problems[] = array ('HOMEDIR_ABSENT');
		}
		else
		{
			foreach ($this->user->vhost as $vhost)
			{
				if (! file_exists ($vhost->path ()))
				{
					if ($fix)
						$vhost->save (); // The vHost file should have been written //
					
					$problems[] = array ('VHOST_FILE_ABSENT', 'vHost configuration file has been regenerated', $vhost);
				}
				else if (preg_match ('#\s*DocumentRoot\s+expired#i', file_get_contents ($vhost->path ())))
				{
					if ($fix)
						$vhost->save (); // The vHost file should be rewritten //
					
					$problems[] = array ('VHOST_NOT_RENEWED', 'vHost configuration file has been regenerated', $vhost);
				}
				
				if (! (file_exists ($vhost->docroot) && is_dir ($vhost->docroot)))
				{
					if (! self::homedirStorageAvailable ($this->user))
					{
						$problems[] = array ('HOMEDIR_STORAGE_UNAVAILABLE');
					}
					else
					{
						if ($fix)
							$status = $this->createDirectory ($vhost->docroot, $this->user, '711');
						
						$problems[] = array ('DOCROOT_ABSENT', 'Document root created' . (isset ($status) && $status['exitcode'] > 0 ? ' (possibly failed)' : ''), $vhost); // The vHost's document root doesn't seem to exist; fixing it automatically can be risky //
					}
				}
			}
			
			if (! (file_exists ($this->user->homedir . '/logs') && is_dir ($this->user->homedir . '/logs')))
			{
				if ($fix)
					$status = $this->createDirectory ($this->user->homedir . '/logs', $this->user, '711');

				$problems[] = array ('LOGS_FOLDER_ABSENT', 'Log folder created' . (isset ($status) && $status['exitcode'] > 0 ? ' (possibly failed)' : ''), $this->user); // Another one who deleted their logs folder... //
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
		
		Log::log ('ProblemSolver has been executed' . (! $fix ? ' (dry run)' : ''), NULL, $data);
		
		return $data;
	}
	
	/**
	 * Whether home directory storage looks mounted.
	 *
	 * This gates creating a missing document root, and the gate matters: if the
	 * filesystem home directories live on is not mounted, every docroot on the server
	 * looks missing at once, and without this the fix would create all of them on
	 * whatever is underneath the mountpoint.
	 *
	 * The old check asked whether a user named `sin` had a home directory -- a canary
	 * account from the original deployment, which no other install has. A user's own
	 * home directory answers the same question without naming anybody: if it is there,
	 * storage is mounted and a missing docroot is genuinely missing. Installs where
	 * that is not enough -- a per-user NAS mount, say -- can point
	 * penguin.storage_check_path at something that only exists when storage is up //
	 */
	private static function homedirStorageAvailable (User $user)
	{
		$checkPath = trim ((string) Config::get ('penguin.storage_check_path'));

		if ($checkPath !== '')
			return file_exists ($checkPath) && is_dir ($checkPath);

		return file_exists ($user->homedir) && is_dir ($user->homedir);
	}

	private function createDirectory ($directory, ?User $owner = NULL, $permissions = NULL)
	{
		$output = array ();
		$cmd2 = $cmd3 = NULL;
		$exitStatus2 = $exitStatus3 = 0;
		
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
				throw new \Exception ('Permissions should be passed as string, just to be safe...');
			
			$cmd3 = 'chmod ' . escapeshellarg ($permissions) . ' ' . escapeshellarg ($directory) . ' 2>&1';
			exec ($cmd3, $output, $exitStatus3);
		}
		
		return array
		(
			'exitcode' => max ($exitStatus1, $exitStatus2, $exitStatus3),
			'command' => array_values (array_filter (array ($cmd1, $cmd2, $cmd3))),
			'output' => implode (PHP_EOL, $output)
		);
	}
}