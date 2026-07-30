<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class Ftp extends LimitedUserOwnedModel
{
	protected $table = 'ftp';
	public $timestamps = false;
	
	protected $hidden = array ('passwd');

	public function setPassword ($password)
	{
		$this->passwd = crypt ($password, '$6$rounds=' . random_int (8000, 12000) . '$' . bin2hex (random_bytes (8)) . '$');
	}
	
	public function url ()
	{
		return route ('staff.ftp.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
}
