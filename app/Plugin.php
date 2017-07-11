<?php

namespace App;

class Plugin
{
	public $name;
	public $title;
	private $namespace;
	public $overrideViews = false;
	private $actions = [];
	private $systemTasks = [];
	
	public static function all ()
	{
		$plugins = [];
		
		$path = resource_path ('plugins/');
		
		foreach (scandir ($path) as $folder)
		{
			if (is_dir ($path . $folder) && ! starts_with ($folder, '.'))
				$plugins[$folder] = self::fromName ($folder);
		}
		
		return $plugins;
	}
	
	public static function fromName ($name)
	{
		$path = resource_path ('plugins/');
		
		if (! (file_exists ($path . $name) && is_dir ($path . $name)))
			throw new \Exception ('Plugin ' . $name . ' not found');
		
		return new self ($name);
	}
	
	public static function executeAllActions ($request, $action, ...$params)
	{
		$responses = [];
		
		foreach (self::all () as $plugin)
		{
			$pluginResponses = $plugin->executeActions ($request, $action, $params);
			if (count ($pluginResponses) > 0)
				array_push ($responses, ...$pluginResponses);
		}
		
		return $responses;
	}
	
	public static function executeAllSystemTasks ($type, $data = [])
	{
		$statuses = [];
		
		foreach (self::all () as $plugin)
		{
			$pluginStatuses = $plugin->executeSystemTasks ($type, $data);
			if (count ($pluginStatuses) > 0)
				array_push ($statuses, ...$pluginStatuses);
		}
		
		return $statuses;
	}
	
	private function __construct ($name)
	{
		$path = resource_path ('plugins/');
		
		$this->name = $name;
		
		$jsonPath = $path . $name . '/plugin.json';
		
		if (file_exists ($jsonPath))
		{
			$data = json_decode (file_get_contents ($jsonPath), true);
			
			if (array_key_exists ('title', $data))
				$this->title = $data['title'];
			
			if (array_key_exists ('namespace', $data))
				$this->namespace = $data['namespace'];
			
			if (array_key_exists ('override_views', $data) && $data['override_views'])
				$this->overrideViews = true;
			
			if (array_key_exists ('actions', $data) && is_array ($data['actions']))
				$this->actions = $data['actions'];
			
			if (array_key_exists ('system_tasks', $data) && is_array ($data['system_tasks']))
				$this->systemTasks = $data['system_tasks'];
		}
		
		if (empty ($this->title))
			$this->title = $this->name;
	}
	
	private function getNamespace ()
	{
		if (empty ($this->namespace))
			return '';
		
		return 'Plugin\\' . $this->namespace . '\\';
	}
	
	public function getFolder ()
	{
		$path = resource_path ('plugins/');
		
		return $path . $this->name . '/';
	}
	
	public function executeActions ($request, $action, ...$params)
	{
		if (count ($this->actions) === 0)
			return;
		
		include_once ($this->getFolder () . 'actions.php');
		$responses = [];
		
		foreach ($this->actions as $actionName => $actionMethods)
		{
			$actionMethods = (array) $actionMethods;
			
			if ($action === $actionName)
			{
				foreach ($actionMethods as $actionMethod)
					$responses[] = call_user_func ($this->getNamespace () . $actionMethod, $request, ...$params);
				
				break;
			}
		}
		
		return $responses;
	}
	
	public function executeSystemTasks ($type, $data = [])
	{
		if (count ($this->systemTasks) === 0)
			return;
		
		include_once ($this->getFolder () . 'system_tasks.php');
		$statuses = [];
		
		foreach ($this->systemTasks as $taskType => $taskMethods)
		{
			$taskMethods = (array) $taskMethods;
			
			if ($type === $taskType)
			{
				foreach ($taskMethods as $taskMethod)
					$statuses[] = call_user_func ($this->getNamespace () . $taskMethod, $data);
				
				break;
			}
		}
		
		return $statuses;
	}
}