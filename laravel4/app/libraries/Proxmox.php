<?php

class Proxmox extends ProxmoxClass
{
	function __construct ($username, $password)
	{
		$this->authenticate ($username, $password);
	}

	public function getNodes ()
	{
		$nodes = array ();
		
		foreach ($this->get ('nodes') as $nodeData)
			$nodes[] = new ProxmoxNode ($this, $nodeData);

		return $nodes;
	}
}
