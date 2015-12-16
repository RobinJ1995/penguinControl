<?php

class SinLog extends Eloquent // Log is een bestaande class in Laravel //
{
	protected $table = 'log';
	public $timestamps = true;
	
	public static function log ($message, $userId = NULL, ...$data)
	{
		$log = new SinLog ();
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
		return $this->belongsTo ('User');
	}
}
