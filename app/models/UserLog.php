<?php

class UserLog extends Eloquent
{
	protected $table = 'user_log';
	public $timestamps = false;
	
	public function userInfo ()
	{
		return $this->belongsTo ('UserInfo');
	}
}
