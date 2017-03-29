<?php

class MailForwardingVirtual extends LimitedUserOwnedModel
{

	protected $table = 'mail_forwarding_virtual';
	public $timestamps = false;
	
	public function mailDomainVirtual()
	{
		return $this->belongsTo('MailDomainVirtual');
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
