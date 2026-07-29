<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class MailDomain extends LimitedUserOwnedModel
{
	protected $table = 'mail_domain';
	public $timestamps = false;
	
	public function mailForward ()
	{
		return $this->hasMany (MailForward::class);
	}
	
	public function mailUser ()
	{
		return $this->hasMany (MailUser::class);
	}
	
	public function user ()
	{
		return $this->belongsTo (User::class, 'uid', 'uid');
	}
	
	public function url ()
	{
		return route ('staff.mail.domain.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
}
