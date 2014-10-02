<?php
require ('../bootstrap/autoload.php');
$app = require_once ('../bootstrap/start.php');

$now = time ();

$tasks = SystemTask::where ('start', '<=', $now)->where ('started', 0)->get ();

foreach ($tasks as $task)
{
	$task->started = 1;
	$task->save ();
	
	$status;
	
	switch ($task->type)
	{
		case 'apache_reload':
			$status = apacheReload ($task);
			break;
		case 'homedir_prepare':
			echo 'Home directory voorbereiden voor <kbd>' . $data['user'] . '</kbd>';
			break;
		case 'nuke_expired_vhosts':
			echo 'Websites van vervallen gebruikers uitschakelen';
			break;
		case 'custom':
			echo 'Commando: <kbd>' . $data['command'] . '</kbd>';
			break;
	}
	
	$task->exitcode = $status['exitcode'];
	if (! empty ($task->data))
	{
		$data = json_decode ($task->data, true);
		$data['output'] = $status['output'];
		$task->data = json_encode ($data);
	}
	$task->started = 0;
	
	$task->save ();
}

function apacheReload (SystemTask $task)
{
	$apache = new ServiceApache ();
	
	return $apache->reload ();
}
?>