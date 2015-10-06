<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UserPrepareHomedirCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:prepareHomedir';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Makes a copy of /etc/skel and changes ownership to give a new user a home directory.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct ()
	{
		parent::__construct ();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire ()
	{
		$username = $this->argument ('user');
		
		$userInfo = UserInfo::where ('username', $username)->firstOrFail ();
		$group = $userInfo->user->primaryGroup;
		$homedir = $userInfo->user->homedir;
		
		if ($userInfo == NULL || $group == NULL)
			throw new Exception ('User or group unknown');
		
		$cmd1 = 'cp -R /etc/skel/ ' . escapeshellarg ($homedir) . ' 2>&1';
		$cmd2 = 'chown ' . escapeshellarg ($username) . ':' . escapeshellarg ($group->name) . ' ' . escapeshellarg ($homedir) . ' -R 2>&1';
		
		$output = array ();
		
		exec ($cmd1, $output, $exitStatus1);
		exec ($cmd2, $output, $exitStatus2);
		
		return var_dump (array
		(
			'exitcode' => max ($exitStatus1, $exitStatus2),
			'command' => array ($cmd1, $cmd2),
			'output' => implode (PHP_EOL, $output)
		));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments ()
	{
		return array
		(
			array ('user', InputArgument::REQUIRED, 'Username'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions ()
	{
		return array ();
	}

}
