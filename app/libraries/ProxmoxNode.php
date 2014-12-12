<?php

class ProxmoxNode extends ProxmoxClass
{
	private $nodeName;
	
	function __construct ($nodeName)
	{
		$this->nodeName = $nodeName;
	}
	
	public function getSysLog ()
	{
		return $this->get ('syslog');
	}
	
	public function getVersion ()
	{
		return $this->get ('version');
	}
	
	public function getStatus ()
	{
		return $this->get ('status');
	}
	
	public function getBootLog ()
	{
		return $this->get ('bootlog');
	}
	
	public function getName ()
	{
		return $this->nodeName;
	}
	
	public function getServices ()
	{
		$services = array ();
		
		foreach ($this->get ('services') as $serviceData)
			$services[] = new ProxmoxNodeService ($this, $serviceData);
		
		return $services;
	}
	
	public function getNetworkInterfaces ()
	{
		return $this->get ('network');
	}
	
	public function getVMs ()
	{
		$vms = array ();
		
		foreach ($this->get ('qemu') as $vm)
			$vms[] = new ProxmoxNodeVM ($this, $vm);
		
		return $vms;
	}
	
	private function get ($url)
	{
		$url = 'nodes/' . $this->nodeName . '/' . $url;
		
		return parent::get ($url);
	}
}