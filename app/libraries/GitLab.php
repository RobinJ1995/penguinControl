<?php

class GitLab
{
	private const $api = 'http://192.168.40.105/api/v3/';
	
	public static function createUser ($email, $password, $username, $name, $admin = false)
	{
		$url = self::api . 'users';
		
		$fields = array
		(
			'private_token' => 'iVzWjNu728iWzNdKaCu-',
			'email' => $email,
			'password' => $password,
			'username' => $username,
			'name' => $name,
			'extern_uid' => $username,
			'admin' => ($admin === true ? 'true' : 'false'),
			'can_create_group' => 'true',
		);
		
		$strFields = '';
		foreach ($fields as $key => $value)
			$strFields .= $key . '=' . urlencode ($value) . '&';
		rtrim ($strFields, '&');
		
		$curl = curl_init ();
		
		curl_setopt ($curl, CURLOPT_URL, $url);
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $strFields);
		
		$result = curl_exec ($curl);
		
		curl_close ($curl);
		
		return json_decode ($result);
	}
}