<?php

class UserLog extends Eloquent
{
	protected $table = 'user_log';
	public $timestamps = false;
	
	public function user_info ()
	{
		return $this->belongsTo ('UserInfo');
	}
}
