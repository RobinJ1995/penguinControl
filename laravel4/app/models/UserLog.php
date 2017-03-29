<?php

class UserLog extends Eloquent
{
	protected $table = 'user_log';
	public $timestamps = false;
	
	public function userInfo ()
	{
		return $this->belongsTo ('UserInfo');
	}
	
	public function url ()
	{
		return action ('StaffUserLogController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
