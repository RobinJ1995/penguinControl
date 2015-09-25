<?php

class FtpUserVirtual extends LimitedUserOwnedModel
{
	protected $table = 'ftp_user_virtual';
	public $timestamps = false;
	
	protected $hidden = array ('passwd');

	public function setPassword ($password)
	{
		$this->passwd = crypt ($password, '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$');
	}
}
