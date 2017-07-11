<?php

namespace Plugin\WordpressInstaller;

use App\Models\Vhost;

require_once ('WordpressManager.php');

function vhost_install_wordpress ($data)
{
	$vhost = Vhost::find ($data['vhostId']);
	$wpman = new WordpressManager ($vhost);

	return $wpman->install ();
}