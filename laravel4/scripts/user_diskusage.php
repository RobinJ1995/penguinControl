<?php
$db = new PDO
(
	'mysql:host=192.168.20.101;dbname=control_new',
	'sin_diskusage',
	'2rquJMezP4eDVcww'
);
$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$output = array ();
exec ('for dir in `echo /mnt/data/home/users/*/*`; do user=`echo $dir | cut -f7 -d"/"`; used=`du -d0 $dir | cut -f1`; echo $user $used; done', $output);

$usage = array ();

foreach ($output as $line)
{
	$arr = explode (' ', $line);
	$user = $arr[0];
	$size = $arr[1];
	
	$usage[$user] = $size;
}

$output = array ();
exec ('for dir in `echo /mnt/data/cloud/data/*`; do user=`echo $dir | cut -f6 -d"/"`; used=`du -d0 $dir | cut -f1`; echo $user $used; done', $output);
foreach ($output as $line)
{
	$arr = explode (' ', $line);
	$user = $arr[0];
	$size = $arr[1];
	
	if (array_key_exists ($user, $usage))
		$usage[$user] += $size;
}

foreach ($usage as $user => $usage)
{
	$q = $db->prepare
	(
		'UPDATE user
		INNER JOIN user_info ON user.user_info_id = user_info.id
		SET diskusage = :usage
		WHERE user_info.username = :user;'
	);
	$q->bindValue (':user', $user);
	$q->bindValue (':usage', $usage / 1000);
	
	$q->execute ();
}
?>
