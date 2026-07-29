<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;

class Group extends BaseModel
{

	protected $table = 'group';
	public $timestamps = false;
	
	public function url ()
	{
		return route ('staff.group.index');
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}

}
