<?php

namespace App\Console\Commands;

use App\Certbot;
use App\Models\Log;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Vhost;
use App\ServiceApache;
use App\WordpressManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CronCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'cron:run';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'SystemTask cron';
	
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
	public function handle ()
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
					Vhost::nukeExpired ();
				case SystemTask::TYPE_APACHE_RELOAD: // Falls through //
					$apache = new ServiceApache ();
					$status = $apache->reload (false);
					break;
				case SystemTask::TYPE_HOMEDIR_PREPARE:
					$userInfo = UserInfo::find ($data['userInfoId']);
					$status = $userInfo->prepareHomedir ();
					break;
				case SystemTask::TYPE_PROBLEM_SOLVER:
					$user = User::find ($data['userId']);
					
					$problemSolver = new ProblemSolver ($user);
					$status = array
					(
						'result' => $problemSolver->run ()
					);
					break;
				case SystemTask::TYPE_CALCULATE_DISK_USAGE:
					User::calculateAndSaveDiskUsage ();
					break;
				case SystemTask::TYPE_CREATE_VHOST_DOCROOT:
					$vhost = Vhost::find ($data['vhostId']);
					$status1 = $vhost->createDocroot ();
					
					$apache = new ServiceApache ();
					$status2 = $apache->reload (false);
					
					$status = [
						'exitcode' => max ($status1['exitcode'], $status2['exitcode']),
						'command' => array_merge ((array) $status1['command'], (array) $status2['command']),
						'output' => array_to_string ($status1['output']) . PHP_EOL . PHP_EOL . array_to_string ($status2['output'])
					];
					break;
				case SystemTask::TYPE_VHOST_INSTALL_WORDPRESS:
					$vhost = Vhost::find ($data['vhostId']);
					$wpman = new WordpressManager ($vhost);
					$status = $wpman->install ();
					
					break;
				case SystemTask::TYPE_VHOST_OBTAIN_CERTIFICATE:
					$vhost = Vhost::find ($data['vhostId']);
					$certbot = new Certbot ($vhost);
					$status = $certbot->obtain ((bool) $data['redirect']);
					
					break;
			}
			
			if (is_array ($status))
			{
				if (array_key_exists ('exitcode', $status))
				{
					$task->exitcode = $status['exitcode'];
					unset ($status['exitcode']);
				}
				else
				{
					$task->exitcode = 0;
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
			
			Log::log ('SystemTask executed', NULL, $task);
		}
	}
}
