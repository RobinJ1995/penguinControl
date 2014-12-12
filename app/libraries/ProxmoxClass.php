<?php

class ProxmoxClass
{
	const API = 'https://127.0.0.1:8006/api2/json/';
	
	protected $token;
	protected $ticket;
	
	protected function authenticate ($username, $password)
	{
		$data = $this->get ('access/ticket', 'username=' . urlencode ($username) . '&password=' . urlencode ($password));
		
		$this->token = $data->CSRFPreventionToken;
		$this->ticket = $data->ticket;
	}
	
	protected function get ($url, $postFields = null)
	{
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, self::API . $url);
		curl_setopt ($curl, CURLOPT_POST, true);
		if (! empty ($postFields))
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($curl, CURLOPT_COOKIEFILE, '../app/storage/proxmox_cookies');
		curl_setopt ($curl, CURLOPT_COOKIEJAR, '../app/storage/proxmox_cookies');
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array ('PVEAuthCookie: ' . $this->ticket, 'CSRFPreventionToken: ' . $this->token));
		
		$json = curl_exec ($curl);
		$error = curl_error ($curl);
		
		curl_close ($curl);
		
		if (empty ($error))
		{
			$data = json_decode ($json);
			
			if (empty ($data))
				return $data;
			else
				return $data->data;
		}
		else
		{
			throw new Exception ($error);
		}
	}
}