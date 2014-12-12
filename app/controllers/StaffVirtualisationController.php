<?php

class StaffVirtualisationController extends BaseController
{
	public function index ()
	{
		$api = new Proxmox ('sincontrol@pve', '***REMOVED***');
		$nodes = $api->getNodes ();
		
		return View::make ('staff.virtualisation.index', compact ('nodes'));
	}
}
