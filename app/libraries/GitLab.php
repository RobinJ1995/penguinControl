<?php

class GitLab
{
	// Productie
	const API = 'http://192.168.20.107/api/v3/';
	const PRIVATE_TOKEN = '***REMOVED***';
	
	// Testing
	//const API = 'http://git.sinners.be/api/v3/';
	//const PRIVATE_TOKEN = '***REMOVED***';
	//const API = 'http://127.0.0.1:8081/api/v3/';
	//const PRIVATE_TOKEN = '***REMOVED***';
	
	private $lastCurlInfo = NULL;
	
	public function createUser ($email, $password, $username, $name, $admin = false)
	{
		$url = self::API . 'users';
		
		$fields = array
		(
			'private_token' => self::PRIVATE_TOKEN,
			'email' => $email,
			'password' => $password,
			'username' => $username,
			'name' => $name,
			'extern_uid' => $username,
			'provider' => 'sincontrol',
			'admin' => ($admin === true ? 'true' : 'false'),
			'can_create_group' => 'true',
			'confirm' => false
		);
		
		//$strFields = $this->serializePost ($fields);
		$strFields = http_build_query ($fields);
		
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $strFields);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec ($curl);
		
		$this->lastCurlInfo = curl_getinfo ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
	
	public function getUser ($id)
	{
		$url = self::API . 'users/'.$id;
		$url.= '?private_token='.self::PRIVATE_TOKEN;
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec ($curl);
		
		$this->lastCurlInfo = curl_getinfo ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
	
	public function getUsers ()
	{
		$url = self::API . 'users/';
		$url.= '?private_token='.self::PRIVATE_TOKEN;
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec ($curl);
		
		$this->lastCurlInfo = curl_getinfo ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
	
	public function changePassword ($id,$password)
	{
		return $this->updateUser ($id, 'password', $password);
	}
	
	public function changeName ($id,$name)
	{
		return $this->updateUser ($id, 'name', $name);
	}
	
	public function changeEmail ($id,$email)
	{
		return $this->updateUser ($id, 'email', $email);
	}
	
	public function changeAdmin ($id,$admin)
	{
		return $this->updateUser ($id, 'admin', $admin);
	}
	
	public function deleteUser ($id)
	{
		$url = self::API . 'users/'.$id.'?private_token='.self::PRIVATE_TOKEN;
		
		$curl = curl_init ();
		$fields = array ('private_token' => self::PRIVATE_TOKEN);
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt ($curl, CURLOPT_POSTFIELDS, json_encode ($fields));
		curl_setopt ($curl, CURLOPT_HEADER, true);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: '.strlen(json_encode ($fields))
		    ));
		
		$result = curl_exec ($curl);
		
		$this->lastCurlInfo = curl_getinfo ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
	
	public function blockUser ($id)
	{
		/*
		$url = self::API . 'users/'.$id.'/block';
		$url .= '?private_token='.self::PRIVATE_TOKEN;
		
		$fields = array ('private_token' => self::PRIVATE_TOKEN);
		
		//$fields[$type]=$value;
		//$strFields = http_build_query($fields);
		//$strFields = $this->serializePost ($fields);
		
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_POST, true);
		//curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		//curl_setopt ($curl, CURLOPT_POSTFIELDS, $strFields);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, json_encode ($fields));
		curl_setopt ($curl, CURLOPT_HEADER, true);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array(
			'PRIVATE-TOKEN: '.self::PRIVATE_TOKEN,
		    'Content-Type: application/json',
		    'Content-Length: '.strlen(json_encode ($fields))
		    ));
		
		$result = curl_exec ($curl);
		
		curl_close ($curl);
		echo "<pre>",print_r($result),"</pre>";
		
		return json_decode ($result);
		 *
		 */
	}
	
	private function updateUser ($id,$type,$value)
	{
		$url = self::API . 'users/'.$id.'?private_token='.self::PRIVATE_TOKEN;
		
		$fields = array ('private_token' => self::PRIVATE_TOKEN);
		
		$fields[$type]=$value;
		
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt ($curl, CURLOPT_POSTFIELDS, json_encode ($fields));
		curl_setopt ($curl, CURLOPT_HEADER, true);
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
		curl_setopt ($curl, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: '.strlen(json_encode ($fields))
		    ));
		
		$result = curl_exec ($curl);
		
		$this->lastCurlInfo = curl_getinfo ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
	
	public function getLastCurlInfo ()
	{
		return $this->lastCurlInfo;
	}

	private function serializePost ($fields)
	{
		$strFields = '';
		foreach ($fields as $key => $value)
			$strFields .= $key . '=' . urlencode ($value) . '&';
		rtrim ($strFields, '&');
		
		return $strFields;
	}
}