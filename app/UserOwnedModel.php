<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class UserOwnedModel extends Model
{
	public function user ()
	{
		return $this->belongsTo('\App\Models\User', 'uid', 'uid');
	}
}