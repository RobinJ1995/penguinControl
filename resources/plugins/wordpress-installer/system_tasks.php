<?php

function vhost_task ($data)
{
	$vhost = \App\Models\Vhost::find ($data['vhostId']);
	$wpman = new \App\WordpressManager ($vhost);

	return $wpman->install ();
}