<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class MailForward extends LimitedUserOwnedModel
{
	protected $table = 'mail_forward';
	public $timestamps = false;
	
	public function mailDomain()
	{
		return $this->belongsTo ('\App\Models\MailDomain');
	}
	
	public function url ()
	{
		return action ('StaffMailForwardingController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}

}
