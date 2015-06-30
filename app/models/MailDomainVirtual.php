<?php

class MailDomainVirtual extends LimitedUserOwnedModel
{
	protected $table = 'mail_domain_virtual';
	public $timestamps = false;
	
	public function mailForwardingVirtual ()
	{
		return $this->hasMany ('MailForwardingVirtual');
	}
	
	public function mailUserVirtual ()
	{
		return $this->hasMany ('MailUserVirtual');
	}
	
	public function user ()
	{
		return $this->belongsTo ('User', 'uid', 'uid');
	}
}
