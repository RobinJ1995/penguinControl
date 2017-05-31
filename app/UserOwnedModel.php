<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class UserOwnedModel extends Model
{
	public function user ()
	{
		return $this->belongsTo ('\App\Models\User', 'uid', 'uid');
	}
	
	public static function accessible ()
	{
		$user = Auth::user ();
		
		$secondary = [];
		if (is_admin ())
			$secondary = self::where ('uid', '!=', $user->uid);
		
		return self::where ('uid', $user->uid)->union ($secondary);
	}
}