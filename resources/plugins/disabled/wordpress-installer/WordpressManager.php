<?php

namespace Plugin\WordpressInstaller;

use App\Models\Vhost;

class WordpressManager
{
	private $vhost;

	public function __construct (Vhost $vhost)
	{
		$this->vhost = $vhost;
	}

	public function install ()
	{
		$username  = $this->vhost->user->userInfo->username;
		$groupName = $this->vhost->user->primaryGroup->name;
		$docroot   = $this->vhost->docroot;

		$cmd = 'cd ' . escapeshellarg ($docroot) . ' && wget https://en-gb.wordpress.org/wordpress-latest-en_GB.tar.gz && tar xf wordpress-latest-en_GB.tar.gz && mv wordpress/* . && rmdir wordpress/ && chown ' . escapeshellarg ($username) . ':' . escapeshellarg ($groupName) . ' ' . escapeshellarg ($docroot) . ' -R 2>&1';
		$output = [];

		exec ($cmd, $output, $exitStatus);

		return array
		(
			'exitcode' => $exitStatus,
			'command'  => $cmd,
			'output'   => implode (PHP_EOL, $output)
		);
	}
}