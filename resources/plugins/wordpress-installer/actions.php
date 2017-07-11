<?php

namespace Plugin\WordpressInstaller;

use App\Alert;
use App\Models\SystemTask;
use App\Models\Vhost;
use Illuminate\Support\Facades\Redirect;

function vhost_store ($request, ...$params)
{
	$lastSaved = Vhost::getLastSaved ();
	
	if (count ($lastSaved) > 0 && $request->input ('installWordpress'))
	{
		$wpTask = new SystemTask ();
		$wpTask->type = 'vhost_install_wordpress';
		$wpTask->data = json_encode (['vhostId' => $lastSaved[0]->id]);
		$wpTask->save ();
		
		return Redirect::to ('/system/systemtask/' . $wpTask->id . '/show')->with ('alerts', array (new Alert ('vHost created', Alert::TYPE_SUCCESS), new Alert ('Wordpress installation pending. This page will show more information once the installation attempt has completed.', Alert::TYPE_INFO)));
	}
}