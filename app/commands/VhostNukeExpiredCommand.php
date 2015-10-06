<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class VhostNukeExpiredCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vhost:nukeExpired';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disables vHosts belonging to expired users.';

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
		ApacheVhostVirtual::nukeExpired ();

		return $this->call ('vhost:apacheReload');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments ()
	{
		return array ();
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
