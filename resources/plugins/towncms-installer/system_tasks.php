<?php

namespace Plugin\TownCMSInstaller;

use App\Models\Vhost;

require_once ('TownCMSManager.php');

function vhost_install_towncms ($data)
{
	$vhost   = Vhost::find ($data['vhostId']);
	$tcmsman = new TownCMSManager ($vhost);

	return $tcmsman->install ();
}