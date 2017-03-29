<?php

class ProxmoxNodeVM extends ProxmoxClass
{
	private $node;
	private $id;
	private $name;
	private $state;
	private $template;
	private $disk;
	private $maxDisk;
	private $maxMem;
	private $cpu;
	private $mem;
	private $cpus;
	private $diskRead;
	private $diskWrite;
	private $netIn;
	private $netOut;
	
	function __construct ($node, $data)
	{
		$node->passAuthentication ($this);
		
		$this->node = $node;
		
		$this->id = $data->vmid;
		$this->name = $data->name;
		$this->state = $data->status;
		$this->template = (bool) $data->template;
		
		$status = $this->get ('status/current');
		$this->disk = $status->disk;
		$this->maxDisk = $status->maxdisk;
		$this->maxMem = $status->maxmem;
		$this->cpu = $status->cpu;
		$this->mem = $status->mem;
		$this->cpus = $status->cpus;
		$this->diskRead = $status->diskread;
		$this->diskWrite = $status->diskwrite;
		$this->netIn = $status->netin;
		$this->netOut = $status->netout;
	}
	
	public function getNode ()
	{
		return $this->node;
	}
	
	public function getId ()
	{
		return $this->id;
	}
	
	public function getName ()
	{
		return $this->name;
	}
	
	public function getState ()
	{
		return $this->state;
	}
	
	public function isTemplate ()
	{
		return $this->template;
	}
	
	public function getDisk ()
	{
		return $this->disk;
	}
	
	public function getMaxDisk ()
	{
		return $this->maxDisk;
	}

	public function getMaxMem ()
	{
		return $this->maxMem;
	}

	public function getCPU ()
	{
		return $this->cpu;
	}

	public function getMem ()
	{
		return $this->mem;
	}

	public function getCPUs ()
	{
		return $this->cpus;
	}

	public function getDiskRead ()
	{
		return $this->diskRead;
	}

	public function getDiskWrite ()
	{
		return $this->diskWrite;
	}

	public function getNetIn ()
	{
		return $this->netIn;
	}

	public function getNetOut ()
	{
		return $this->netOut;
	}
	
	public function getMemoryUsage ()
	{
		return round ($this->getMem () / $this->getMaxMem () * 100, 2);
	}
	
	public function getDiskUsage ()
	{
		return round ($this->getDisk () / $this->getMaxDisk () * 100, 2);
	}
	
	public function getCPUUsage ()
	{
		return round ($this->getCPU () * 100, 2);
	}
	
	public function reset ()
	{
		return $this->get ('status/reset');
	}
	
	public function resume ()
	{
		return $this->get ('status/resume');
	}
	
	public function shutdown ()
	{
		return $this->get ('status/shutdown');
	}
	
	public function start ()
	{
		return $this->get ('status/start');
	}
	
	public function stop ()
	{
		return $this->get ('status/stop');
	}
	
	public function suspend ()
	{
		return $this->get ('suspend');
	}
	
	protected function get ($url, $postFields = null)
	{
		$url = 'nodes/' . $this->node->getName () . '/qemu/' . $this->id . '/' . $url;
		
		return parent::get ($url, $postFields);
	}
}