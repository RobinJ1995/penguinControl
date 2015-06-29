<?php
require_once ('SystemTask.php');
require_once ('ApacheVhostVirtual.php');
require_once ('ServiceApache.php');

$db = new PDO
(
	'mysql:host=192.168.20.101;dbname=control_new',
	'control_dev',
	'***REMOVED***'
);
$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$now = time ();

$tasks = SystemTask::get ($db);
echo 'voor foreach'.PHP_EOL;
foreach ($tasks as $task)
{
	$task->started = 1;
	$task->save ($db);
	echo 'system task:'.$task->id.PHP_EOL;
	$status;
	
	$data = $task->data;
	if (! empty ($data))
		$data = json_decode ($task->data, true);
	else
		$data = array ();
	
	switch ($task->type)
	{
		case 'apache_reload':
			$status = apacheReload ();
			break;
		case 'homedir_prepare':
			$status = prepareHomedir ($data['user'], $data['group'], $data['homedir']);
			break;
		case 'nuke_expired_vhosts':
			$status = nukeExpiredVHosts ($db);
			break;
		case 'custom':
			$status = runCommand ($data['command']);
			break;
	}
	
	$task->exitcode = $status['exitcode'];
	$data['output'] = $status['output'];
	$task->data = json_encode ($data);
	$task->started = 0;
	$task->lastRun = time();
	
	$task->save ($db);
}
echo 'na foreach'.PHP_EOL;
function apacheReload ()
{
	$apache = new ServiceApache ();
	
	return $apache->reload ();
}

function homedir_prepare ($user, $group, $homedir)
{
	if (empty ($user))
		return array
		(
			'exitcode' => 1,
			'output' => '$user is leeg'
		);
	else if (empty ($group))
		return array
		(
			'exitcode' => 1,
			'output' => '$group is leeg'
		);
	else if (empty ($homedir))
		return array
		(
			'exitcode' => 1,
			'output' => '$homedir is leeg'
		);
	
	$cmd1 = 'cp -R /etc/skel/ ' . $homedir . ' 2>&1';
	$cmd2 = 'chown ' . $username . ':' . $group . ' ' . $homedir . ' -R 2>&1';
	
	exec ($cmd1, $output, $exitStatus1);
	exec ($cmd2, $output, $exitStatus2);
	
	return array
	(
		'exitcode' => max ($exitStatus1, $exitStatus2),
		'output' => implode (PHP_EOL, $output)
	);
}

function nukeExpiredVHosts ($db)
{
	$vHosts = ApacheVhostVirtual::get ($db);
	
	$exitStatus = 0;
	$output = '';
	
	foreach ($vHosts as $vHost)
	{
		$status = $vHost->nuke ();
		
		$exitStatus = max ($exitStatus, $status['exitcode']);
		if (! empty ($status['output']))
			$output .= $status['output'] . PHP_EOL;
	}
	
	return array
	(
		'exitcode' => $exitStatus,
		'output' => $output
	);
}

function runCommand ($command)
{
	exec ($command . ' 2>&1', $output, $exitStatus);
	
	return array
	(
		'exitcode' => $exitStatus,
		'output' => implode (PHP_EOL, $output)
	);
}
