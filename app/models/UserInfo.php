<?php

class UserInfo extends Eloquent
{

	protected $table = 'user_info';
	public $timestamps = false;
	protected $hidden = array ('validationcode', 'logintoken', 'etc');
	
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
	
	public function generateValidationCode ()
	{
		$this->validationcode = bin2hex (openssl_random_pseudo_bytes (16));
		
		return $this->validationcode;
	}
	
	public function generateLoginToken ()
	{
		$this->logintoken = bin2hex (openssl_random_pseudo_bytes (16));
		
		return $this->logintoken;
	}
	
	public function url ()
	{
		if ($this->user != NULL)
			return action ('StaffUserController@more', $this->user->id);
		
		return NULL;
	}
	
	public function link ()
	{
		$url = $this->url ();
		$caption = get_class () . '#' . $this->id . ' (' . $this->username . ')';
		
		if ($url == NULL)
			return $caption;
		
		return '<a href="' . $url . '">' . $caption . '</a>';
	}
}
