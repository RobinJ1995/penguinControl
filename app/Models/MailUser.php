<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class MailUser extends LimitedUserOwnedModel
{
	protected $table = 'mail_user';
	public $timestamps = false;
	
	protected $hidden = array ('password');

	public function setPassword ($password)
	{
		$this->password = crypt ($password, '$6$rounds=' . random_int (8000, 12000) . '$' . bin2hex (random_bytes (8)) . '$');
	}
	
	public function mailDomain()
	{
		return $this->belongsTo (MailDomain::class);
	}
	
	public function url ()
	{
		return route ('staff.mail.user.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
}
