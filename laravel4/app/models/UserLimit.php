<?php

class UserLimit extends UserOwnedModel
{
	protected $table = 'user_limit';
	public $timestamps = false;
	
	public static function getLimit (User $user, $property)
	{
		$limit = UserLimit::where ('uid', $user->uid);
		if ($limit->count () < 1)
			$limit = UserLimit::whereNull ('uid');
		
		return $limit->pluck ($property);
	}
	
	public static function getGlobalLimit ($property)
	{
		return UserLimit::whereNull ('uid')->pluck ($property);
	}
	
	public function url ()
	{
		return action ('StaffUserLimitController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
