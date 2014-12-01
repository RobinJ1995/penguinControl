<?php

class UserLog extends UserOwnedModel
{
	protected $table = 'user_log';
	public $timestamps = false;
	
	public function user_info(){
		return $this->belongsTo ('UserInfo');
	}
}
