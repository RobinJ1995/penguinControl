<?php

class MailForwardingVirtual extends LimitedUserOwnedModel
{

	protected $table = 'mail_forwarding_virtual';
	public $timestamps = false;
	
	public function mailDomainVirtual()
	{
		return $this->belongsTo('MailDomainVirtual');
	}

}
