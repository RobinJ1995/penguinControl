<?php

class Proxmox extends ProxmoxClass
{
	function __construct ($username, $password)
	{
		$curl = curl_init ();

		curl_setopt ($curl, CURLOPT_URL, ProxmoxClass::API . 'access/ticket');
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, 'username=' . urlencode ($username) . '&password=' . urlencode ($password));
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, false);

		$data = curl_exec ($curl);
		
		die (var_dump ($data));
		
		curl_close ($curl);
	}

	public function getNodes ()
	{
		$nodes = array ();

		foreach ($this->get ('nodes') as $nodeData)
			$nodes[] = new ProxmoxNode (substr ($nodeData['id'], 5));

		return $nodes;
	}

}
