<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Log extends BaseModel
{
	protected $table = 'log';
	public $timestamps = true;
	
	public static function log ($message, $userId = NULL, ...$data)
	{
		$log = new Log ();
		$log->message = $message;
		
		if ($userId == NULL)
		{
			$user = Auth::user ();
			if ($user != null)
				$log->user_id = $user->id;
		}
		else
		{
			$log->user_id = $userId;
		}
		
		if (! empty ($data))
			$log->data = json_encode ($data);
		
		$log->save ();
	}
	
	public function user ()
	{
		return $this->belongsTo ('App\Models\User');
	}
}
