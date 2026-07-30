<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class UserOwnedModel extends BaseModel
{
	public function user ()
	{
		return $this->belongsTo (User::class, 'uid', 'uid');
	}
	
	public static function accessible ()
	{
		$user = Auth::user ();
		
		$results = self::where ('uid', $user->uid);
		
		if (is_admin ())
			return $results->union (self::where ('uid', '!=', $user->uid));
		
		return $results;
	}
}