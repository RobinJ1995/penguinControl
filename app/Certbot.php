<?php

namespace App;

use App\Models\Vhost;

class Certbot
{
	private $vhost;
	
	public function __construct (Vhost $vhost)
	{
		$this->vhost = $vhost;
	}
	
	public function obtain ($redirect = false)
	{
		$hosts = array ($this->vhost->servername);
		if ($this->vhost->serveralias)
			$hosts = array_merge ($hosts, preg_split ('/\s+/', trim ($this->vhost->serveralias)));
		
		$domains = array ();
		foreach (array_filter ($hosts) as $host)
			$domains[] = '-d ' . escapeshellarg ($host);
		
		$cmd = 'certbot --apache -n ' . implode (' ', $domains) . ($redirect ? ' --redirect' : '') . ' 2>&1';
		$output = [];
		$exitStatus = NULL;
		
		exec ($cmd, $output, $exitStatus);
		
		return array
		(
			'exitcode' => $exitStatus,
			'command' => $cmd,
			'output' => implode (PHP_EOL, $output)
		);
	}
}