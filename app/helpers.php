<?php

function array_to_string ($arr)
{
	if (is_string ($arr))
		return $arr;
	
	return implode (PHP_EOL, $arr);
}

function htmlstr (string $str)
{
	return new \Illuminate\Support\HtmlString ($str);
}

function trailing_slash ($path)
{
	if (! ends_with ($path, '/'))
		$path .= '/';
	
	return $path;
}

function array_except_value (array $arr, $except)
{
	return array_diff ($arr, (array) $except);
}

function array_string_prepend (array $arr, string $str)
{
	foreach ($arr as &$item)
		$item = $str . $item;
	
	return $arr;
}

function array_keyval_combine (array $arr)
{
	return array_combine ($arr, $arr);
}

function is_feature_enabled (string $featureName)
{
	return config ('penguin.' . $featureName, false);
}

function is_admin ($user = NULL)
{
	if ($user === NULL)
		$user = \Illuminate\Support\Facades\Auth::user ();
	
	return ($user !== NULL && $user->isAdmin ());
}

function is_owner ($resource, $user = NULL)
{
	if ($user === NULL)
		$user = \Illuminate\Support\Facades\Auth::user ();
	
	return ($user !== NULL && $user->uid === $resource->uid);
}

function prohibited_usernames (bool $returnString = false)
{
	$reservedUsernames = array ('ns', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5', 'control', 'penguincontrol', 'admin', 'administrator', 'root', 'srv', 'intern', 'extern', 'git', 'svn', 'db', 'database', 'web', 'mail', 'ssh', 'shell', 'cloud', 'voice', 'docu');
	$etcPasswd = explode (PHP_EOL, file_get_contents ('/etc/passwd'));
	
	foreach ($etcPasswd as $entry)
	{
		if (! empty ($entry))
		{
			$fields = explode (':', $entry, 2);
			
			$reservedUsernames[] = $fields[0];
		}
	}
	
	$whoami = `whoami`;
	if (! empty ($whoami))
		$reservedUsernames[] = $whoami;
	
	if ($returnString)
		return implode (',', $reservedUsernames);
	
	return $reservedUsernames;
}