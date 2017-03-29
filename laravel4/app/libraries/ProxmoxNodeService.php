<?php

class ProxmoxNodeService extends ProxmoxClass
{
	private $node;
	private $serviceName;
	private $friendlyName;
	private $description;
	private $state;
	
	function __construct ($api, $node, $data)
	{
		$api->passAuthentication ($this);
		
		$this->node = $node;
		
		$this->serviceName = $data->service;
		$this->friendlyName = $data->name;
		$this->description = $data->desc;
		$this->state = $data->state;
	}
	
	public function getNode ()
	{
		return $this->node;
	}
	
	public function getName ()
	{
		return $this->serviceName;
	}
	
	public function getFriendlyName ()
	{
		return $this->friendlyName;
	}
	
	public function getDescription ()
	{
		return $this->description;
	}
	
	public function getState ()
	{
		return $this->state;
	}
	
	public function reload ()
	{
		return $this->get ('reload');
	}
	
	public function restart ()
	{
		return $this->get ('restart');
	}
	
	public function start ()
	{
		return $this->get ('start');
	}
	
	public function stop ()
	{
		return $this->get ('stop');
	}
	
	protected function get ($url, $postFields = null)
	{
		$url = 'nodes/' . $this->node->getName () . '/services/' . $this->serviceName . '/' . $url;
		
		return parent::get ($url, $postFields);
	}
}