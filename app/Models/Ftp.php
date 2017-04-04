<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class Ftp extends LimitedUserOwnedModel
{
	protected $table = 'ftp';
	public $timestamps = false;
	
	protected $hidden = array ('password');

	public function setPassword ($password)
	{
		$this->passwd = crypt ($password, '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$');
	}
	
	public function url ()
	{
		return action ('StaffFtpController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
