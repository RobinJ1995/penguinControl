<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronCommand extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cron:run';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'SystemTask cron task';

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
		$tasks = SystemTask::where
			(
				function ($query)
				{
					$query->where ('start', '<', time ())
						->orWhereNull ('start');
				}
			)->where
			(
				function ($query)
				{
					$query->where ('end', '>=', time ())
						->orWhereNull ('end');
				}
			)->where
			(
				function ($query)
				{
					$query->where ('lastRun', '<', DB::raw (time () . ' - `interval`'))
						->orWhereNull ('lastRun');
				}
			)->where ('started', 0)
			->get ();
		
		foreach ($tasks as $task)
		{	
			$task->started = 1;
			$task->save ();
			$status = NULL;
			
			$data = $task->data;
			if (! empty ($task->data))
				$data = json_decode ($task->data, true);
			else
				$data = array ();
			
			switch ($task->type)
			{
				case SystemTask::TYPE_NUKE_EXPIRED_VHOSTS:
					ApacheVhostVirtual::nukeExpired ();
				case SystemTask::TYPE_APACHE_RELOAD: // Falls through //
					$apache = new ServiceApache ();
					$status = $apache->reload ();
					break;
				case SystemTask::TYPE_HOMEDIR_PREPARE:
					$userInfo = UserInfo::find ($data['userInfoId']);
					$status = $userInfo->prepareHomedir ();
					break;
				case SystemTask::TYPE_PROBLEM_SOLVER:
					$user = User::find ($data['userId']);
		
					$problemSolver = new ProblemSolver ($user);
					$status = $problemSolver->run ();
					break;
			}
			
			if (is_array ($status))
			{
				if (array_key_exists ('exitcode', $status))
				{
					$task->exitcode = $status['exitcode'];
					unset ($status['exitcode']);
				}
				$data = array_merge ($data, $status);
			}
			else if (is_string ($status))
			{
				$data['output'] = $status;
			}
			
			$task->data = json_encode ($data);
			$task->started = 0;
			$task->lastRun = time ();
			
			$task->save ();
			
			SinLog ('SystemTask#' . $task->id . ' uitgevoerd', NULL, $task);
		}
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
