<?php

class ProxmoxClass
{
	const API = 'https://127.0.0.1:8006/api2/json/';
	
	protected function get ($url)
	{
		$options = array
		(
			'ssl' => array
			(
				'verify_peer' => false,
				'verify_peer_name' => false
			)
		);
		
		$json = file_get_contents (self::API . $url, false,  stream_context_create ($options));
		$data = json_decode ($json);
		
		return $data->data;
	}
}