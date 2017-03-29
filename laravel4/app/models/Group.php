<?php

class Group extends Eloquent
{

	protected $table = 'group';
	public $timestamps = false;
	
	public function url ()
	{
		return action ('StaffGroupController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}

}
