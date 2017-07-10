<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
	private static $lastSaved = [];
	
	public static function getLastSaved ($all = false)
	{
		$results = [];
		
		foreach (self::$lastSaved as $model)
		{
			if ($all || (get_class ($model) === get_called_class ()))
				$results[] = $model;
		}
		
		return $results;
	}
	
	public function save (array $options = [])
	{
		$returnValue = parent::save ($options);
		
		self::$lastSaved[] = $this;
		
		return $returnValue;
	}
}
