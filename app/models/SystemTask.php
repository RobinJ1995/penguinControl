<?php

class SystemTask extends Eloquent
{
	protected $table = 'system_task';
	public $timestamps = false;
	
	const TYPE_APACHE_RELOAD = 'apache_reload';
	const TYPE_NUKE_EXPIRED_VHOSTS = 'nuke_expired_vhosts';
	const TYPE_HOMEDIR_PREPARE = 'homedir_prepare';
	const TYPE_PROBLEM_SOLVER = 'problem_solver';
	const TYPE_CALCULATE_DISK_USAGE = 'calculate_disk_usage';
	
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
			$str .= $weeks . ' weken';
		if (! empty ($days))
			$str .= $days . ' dagen';
		if (! empty ($hours))
			$str .= $hours . ' uur';
		if (! empty ($mins))
			$str .= $mins . ' minuten';
		if (! empty ($secs))
			$str .= $secs . ' seconden';

		return $str;
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
