<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ProblemSolverCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'problemsolver:run';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Attempts to automatically fix common problems.';

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
		$user = $userInfo->user;
		
		$problemSolver = new ProblemSolver ($user);
		
		return $problemSolver->run ();
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
