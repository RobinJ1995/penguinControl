<?php
$username = $argv[1];
$homedir = $argv[2];
$group = $argv[3];

if (empty ($username))
	die ('1' . PHP_EOL . '1' . PHP_EOL . 'Empty username');
else if (empty ($homedir))
	die ('1' . PHP_EOL . '1' . PHP_EOL . 'Empty homedir');
else if (empty ($group))
	die ('1' . PHP_EOL . '1' . PHP_EOL . 'Empty group');

$cmd1 = 'sudo cp -R /etc/skel/ ' . escapeshellarg ($homedir) . ' 2>&1';
$cmd2 = 'sudo chown ' . $username . ':' . $group . ' ' . $homedir . ' -R 2>&1';

exec ($cmd1, $output, $exitStatus1);
exec ($cmd2, $output, $exitStatus2);

echo $exitStatus1 . PHP_EOL . $exitStatus2 . PHP_EOL . implode (PHP_EOL, $output);
?>
