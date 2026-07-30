<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class MailForward extends LimitedUserOwnedModel
{
	protected $table = 'mail_forward';
	public $timestamps = false;
	
	public function mailDomain()
	{
		return $this->belongsTo (MailDomain::class);
	}
	
	public function url ()
	{
		return route ('staff.mail.forward.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}

}
