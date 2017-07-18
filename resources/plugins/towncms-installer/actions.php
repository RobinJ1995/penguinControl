<?php

namespace Plugin\TownCMSInstaller;

use App\Alert;
use App\Models\SystemTask;
use App\Models\Vhost;
use Illuminate\Support\Facades\Redirect;

function vhost_store ($request, ...$params)
{
	$lastSaved = Vhost::getLastSaved ();

	if (count ($lastSaved) > 0 && $request->input ('installTownCMS'))
	{
		$tcmsTask       = new SystemTask ();
		$tcmsTask->type = 'vhost_install_towncms';
		$tcmsTask->data = json_encode (['vhostId' => $lastSaved[0]->id]);
		$tcmsTask->save ();

		return Redirect::to ('/system/systemtask/' . $tcmsTask->id . '/show')->with ('alerts', array(new Alert ('vHost created', Alert::TYPE_SUCCESS), new Alert ('Town CMS installation pending. This page will show more information once the installation attempt has completed.', Alert::TYPE_INFO)));
	}
}