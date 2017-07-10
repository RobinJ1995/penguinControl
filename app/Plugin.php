<?php

namespace App;

class Plugin
{
    public $name;
    public $title;
    public $overrideViews = false;
    private $actions = [];
    private $tasks = [];

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

	public static function executeAllTasks ($type, $data = [])
	{
		$statuses = [];

		foreach (self::all () as $plugin)
		{
			$pluginStatuses = $plugin->executeTasks ($type, $data);
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

            if (array_key_exists ('override_views', $data) && $data['override_views'])
                $this->overrideViews = true;

            if (array_key_exists ('actions', $data) && is_array ($data['actions']))
                $this->actions = $data['actions'];
        }

        if (empty ($this->title))
            $this->title = $this->name;
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
                    $responses[] = call_user_func ($actionMethod, $request, ...$params);

                break;
            }
        }

        return $responses;
    }

	public function executeTasks ($type, $data = [])
	{
		if (count ($this->tasks) === 0)
			return;

		include_once ($this->getFolder () . 'system_tasks.php');
		$statuses = [];

		foreach ($this->tasks as $taskType => $taskMethods)
		{
			$taskMethods = (array) $taskMethods;

			if ($type === $taskType)
			{
				foreach ($taskMethods as $taskMethod)
					$statuses[] = call_user_func ($taskMethod, $data);

				break;
			}
		}

		return $statuses;
	}
}