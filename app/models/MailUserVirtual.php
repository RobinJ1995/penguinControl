<?php

class MailUserVirtual extends LimitedUserOwnedModel
{
	protected $table = 'mail_user_virtual';
	public $timestamps = false;
	
	protected $hidden = array ('password');

	public function setPassword ($password)
	{
		$this->password = crypt ($password, '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$');
	}
	
	public function mailDomainVirtual()
	{
		return $this->belongsTo('MailDomainVirtual');
	}
	
	public function url ()
	{
		return action ('StaffMailUserController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
