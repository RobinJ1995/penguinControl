<?php

class ProxmoxNode extends ProxmoxClass
{
	private $nodeName;
	private $disk;
	private $cpu;
	private $maxDisk;
	private $maxMem;
	private $maxCPU;
	private $uptime;
	private $mem;

	function __construct ($api, $data)
	{
		$api->passAuthentication ($this);
		
		$this->nodeName = $data->node;
		$this->disk = $data->disk;
		$this->cpu = $data->cpu;
		$this->maxDisk = $data->maxdisk;
		$this->maxMem = $data->maxmem;
		$this->maxCPU = $data->maxcpu;
		$this->uptime = $data->uptime;
		$this->mem = $data->mem;
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
	
	public function getDisk ()
	{
		return $this->disk;
	}

	public function getCPU ()
	{
		return $this->cpu;
	}

	public function getMaxDisk ()
	{
		return $this->maxDisk;
	}

	public function getMaxMem ()
	{
		return $this->maxMem;
	}

	public function getMaxCPU ()
	{
		return $this->maxCPU;
	}

	public function getUptime ()
	{
		return $this->uptime;
	}
	
	public function getUptimeDays ()
	{
		return round ($this->uptime / 60 / 60 / 24, 0);
	}

	public function getMem ()
	{
		return $this->mem;
	}
	
	public function getCPUUsage ()
	{
		return round ($this->getCPU () / $this->getMaxCPU () * 1000, 2);
	}
	
	public function getMemoryUsage ()
	{
		return round ($this->getMem () / $this->getMaxMem () * 100, 2);
	}
	
	public function getDiskUsage ()
	{
		return round ($this->getDisk () / $this->getMaxDisk () * 100, 2);
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
	
	protected function get ($url, $postFields = null)
	{
		$url = 'nodes/' . $this->nodeName . '/' . $url;
		
		return parent::get ($url, $postFields);
	}
}