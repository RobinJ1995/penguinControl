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
	if (! \Illuminate\Support\Str::endsWith ($path, '/'))
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

/**
 * The login shells a user may be given, as path => label.
 *
 * There used to be three lists that disagreed about whether fish and zsh live in /bin or
 * /usr/bin: two validators said /usr/bin, a third said /bin, and the dropdowns offered a
 * third combination again -- so picking "Fish" on the staff create screen failed that
 * screen's own validation //
 */
function allowed_shells ()
{
	return (array) \Illuminate\Support\Facades\Config::get ('penguin.shells', array ());
}

/**
 * The same list as a validation rule.
 */
function allowed_shells_rule ()
{
	return 'in:' . implode (',', array_keys (allowed_shells ()));
}

function prohibited_usernames (bool $returnString = false)
{
	// There used to be a second, longer copy of this list in StaffUserController, which
	// reserved names belonging to the original deployment. See penguin.reserved_usernames //
	$reservedUsernames = (array) \Illuminate\Support\Facades\Config::get ('penguin.reserved_usernames', array ());
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