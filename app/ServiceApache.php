<?php

namespace App;

class ServiceApache extends SystemService
{
	protected $name = 'Web server'; //EXAMPLE// Web server //
	protected $serverName = 'Web'; //EXAMPLE// Xena //
	protected $software = 'apache2'; //EXAMPLE// apache2 // Service name //
	protected $needsSudo = false; // Whether sudo must be prefixed to the command //
	
	public function reload ($returnAsString = true)
	{
		return $this->cmd ('reload', $returnAsString);
	}
}
