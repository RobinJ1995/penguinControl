<?php

class SinLog extends Eloquent // Log is een bestaande class in Laravel //
{
	protected $table = 'log';
	public $timestamps = true;
	
	public static function log ($message, ...$data)
	{
		$log = new SinLog ();
		$log->message = $message;
		
		$user = Auth::user ();
		if ($user != null)
			$log->user_id = $user->id;
		
		if (! empty ($data))
			$log->data = json_encode ($data);
		
		$log->save ();
	}
	
	public function user ()
	{
		return $this->belongsTo ('User');
	}
}
