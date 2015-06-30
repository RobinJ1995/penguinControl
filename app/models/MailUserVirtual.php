<?php

class MailUserVirtual extends LimitedUserOwnedModel
{
	protected $table = 'mail_user_virtual';
	public $timestamps = false;

	public function setPassword ($password)
	{
		$this->password = crypt ($password, '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$');
	}
	
	public function mailDomainVirtual()
	{
		return $this->belongsTo('MailDomainVirtual');
	}
	
	
}
