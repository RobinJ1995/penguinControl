<?php

class ProxmoxClass
{
	const API = 'https://192.168.80.21:8006/api2/json/';
	
	protected $token;
	protected $ticket;
	
	protected function authenticate ($username, $password)
	{
		$data = $this->get ('access/ticket', 'username=' . urlencode ($username) . '&password=' . urlencode ($password));
		
		$this->token = $data->CSRFPreventionToken;
		$this->ticket = $data->ticket;
	}
	
	public function passAuthentication (ProxmoxClass $api)
	{
		$api->receiveAuthentication ($this->token, $this->ticket);
	}
	
	public function receiveAuthentication ($token, $ticket)
	{
		$this->token = $token;
		$this->ticket = $ticket;
	}
	
	protected function get ($url, $postFields = null)
	{
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, self::API . $url);
		if (! empty ($postFields))
		{
			curl_setopt ($curl, CURLOPT_POST, true);
			curl_setopt ($curl, CURLOPT_POSTFIELDS, $postFields);
		}
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array ('CSRFPreventionToken: ' . $this->token));
		curl_setopt ($curl, CURLOPT_COOKIE, 'PVEAuthCookie=' . $this->ticket);
		
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