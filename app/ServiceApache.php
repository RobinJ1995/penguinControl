<?php

namespace App;

class ServiceApache extends SystemService
{
	protected $name = 'Web server'; //EXAMPLE// Web server //
	protected $serverName = 'Web'; //EXAMPLE// Xena //
	protected $software = 'apache2'; //EXAMPLE// apache2 // Service-naam //
	protected $ssh = null; //EXAMPLE// squid // app/config/remote.php //
	protected $needsSudo = false; // Of sudo voor het commando moet worden gezet //
	
	public function reload ()
	{
		return $this->cmd ('reload', true);
	}
}
