<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;

class UserLog extends BaseModel
{
	protected $table = 'user_log';
	public $timestamps = false;
	
	public function userInfo ()
	{
		return $this->belongsTo (UserInfo::class);
	}
	
	public function url ()
	{
		return route ('staff.user-log.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
}
