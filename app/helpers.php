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