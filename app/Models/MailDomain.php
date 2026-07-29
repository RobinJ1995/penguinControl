<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class MailDomain extends LimitedUserOwnedModel
{
	protected $table = 'mail_domain';
	public $timestamps = false;
	
	public function mailForward ()
	{
		return $this->hasMany ('\App\Models\MailForward');
	}
	
	public function mailUser ()
	{
		return $this->hasMany ('\App\Models\MailUser');
	}
	
	public function user ()
	{
		return $this->belongsTo ('\App\Models\User', 'uid', 'uid');
	}
	
	public function url ()
	{
		return route ('staff.mail.domain.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
