<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;

class SystemTask extends BaseModel
{
	protected $table = 'system_task';
	public $timestamps = false;
	
	const TYPE_APACHE_RELOAD = 'apache_reload';
	const TYPE_NUKE_EXPIRED_VHOSTS = 'nuke_expired_vhosts';
	const TYPE_HOMEDIR_PREPARE = 'homedir_prepare';
	const TYPE_PROBLEM_SOLVER = 'problem_solver';
	const TYPE_CALCULATE_DISK_USAGE = 'calculate_disk_usage';
	const TYPE_CREATE_VHOST_DOCROOT = 'create_vhost_docroot';
	const TYPE_VHOST_OBTAIN_CERTIFICATE = 'vhost_obtain_certificate';
	
	public function interval ()
	{
		return SystemTask::friendlyInterval ($this->interval);
	}
	
	public static function friendlyInterval ($interval)
	{
		$secs = floor ($interval % 60);
		$mins = floor (($interval % 3600) / 60);
		$hours = floor (($interval % 86400) / 3600);
		$days = floor (($interval % 2592000) / 86400);
		$weeks = floor (($interval % 41944000) / 2592000);

		$str = '';

		if (! empty ($weeks))
			$str .= $weeks . ' weeks';
		if (! empty ($days))
			$str .= $days . ' days';
		if (! empty ($hours))
			$str .= $hours . ' hours';
		if (! empty ($mins))
			$str .= $mins . ' minutes';
		if (! empty ($secs))
			$str .= $secs . ' seconds';

		return $str;
	}
	
	public function getTitle ()
	{
		$data = json_decode ($this->data, true);
		
		switch ($this->type)
		{
			case SystemTask::TYPE_APACHE_RELOAD:
				return 'Reload web server configuration';
			case SystemTask::TYPE_HOMEDIR_PREPARE:
				return 'Prepare home directory for <kbd>' . $data['user'] . '</kbd>';
			case SystemTask::TYPE_NUKE_EXPIRED_VHOSTS:
				return 'Disable expired users\' websites';
			case SystemTask::TYPE_PROBLEM_SOLVER:
				return 'Attempt to automatically fix common problems for <kbd>User#' . $data['userId'] . '</kbd>';
			case SystemTask::TYPE_CALCULATE_DISK_USAGE:
				return 'Recalculate users\' disk usage';
			case SystemTask::TYPE_CREATE_VHOST_DOCROOT:
				return 'Create document root for <kbd>vHost#' . $data['vhostId'] . '</kbd>';
			case 'vhost_install_wordpress': //TODO// Stick this into the plugin system //
				return 'Install Wordpress on <kbd>vHost#' . $data['vhostId'] . '</kbd>';
			case 'vhost_install_towncms':
				return 'Install Town CMS on <kbd>vHost#' . $data['vhostId'] . '</kbd>';
			case SystemTask::TYPE_VHOST_OBTAIN_CERTIFICATE:
				return 'Obtain certificate for <kbd>vHost#' . $data['vhostId'] . '</kbd>';
		}
	}
	
	public function url ()
	{
		return action ('StaffSystemTaskController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
	}
}
