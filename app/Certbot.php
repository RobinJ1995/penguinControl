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
		$domains = '-d ' . $this->vhost->servername;
		if ($this->vhost->serveralias)
			$domains .= ' -d ' . str_replace (' ', ' -d ', $this->vhost->serveralias);
		
		$cmd = 'certbot --apache -n ' . $domains . ($redirect ? ' --redirect' : '') . ' 2>&1';
		$output = [];
		
		exec ($cmd, $output, $exitStatus);
		
		return array
		(
			'exitcode' => $exitStatus,
			'command' => $cmd,
			'output' => implode (PHP_EOL, $output)
		);
	}
}