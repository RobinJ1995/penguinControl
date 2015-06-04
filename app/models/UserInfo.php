<?php

class UserInfo extends Eloquent
{

	protected $table = 'user_info';
	public $timestamps = false;
	protected $hidden = array ('etc');
	
	public function getUser ()
	{
		return User::where ('user_info_id', $this->id)->first ();
	}
	
	public function userExists ()
	{
		return (User::where ('user_info_id', $this->id)->count () > 0);
	}
	
	public function userLog ()
	{
		return $this->hasMany ('UserLog');
	}
	
	public function user ()
	{
		return $this->hasOne ('User');
	}

	public function getFullName ()
	{
		return $this->fname . ' ' . $this->lname;
	}
}
