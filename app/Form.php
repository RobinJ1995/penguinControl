<?php
/**
 * Compatibility class for Laravel 4's HTML & Form components
 */

namespace App;

use Illuminate\Support\HtmlString;

class Form
{
	public static function token ()
	{
		return csrf_field ();
	}
	
	public static function select ($name, $items = [], $selected = NULL)
	{
		$html = '<select name="' . e ($name) . '">' . PHP_EOL;
		
		foreach ($items as $value => $title)
		{
			$html .= '<option value="' . e ($value) . '"';
			
			if ($value === $selected)
				$html .= ' selected';
			
			$html .= '>' . e ($title) . '</option>' . PHP_EOL;
		}
		
		$html .= '</select>';
		
		return new HtmlString ($html);
	}
	
	public static function selectRange ($name, $begin, $end, $selected = NULL)
	{
		$range = range ($begin, $end);
		
		return self::select ($name, array_combine ($range, $range), $selected);
	}
	
	public static function hidden ($name, $value)
	{
		return new HtmlString ('<input type="hidden" name="' . e ($name) . '" value="' . e ($value) . '" />');
	}
}