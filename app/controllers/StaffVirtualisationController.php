<?php

class StaffVirtualisationController extends BaseController
{
	public function index ()
	{
		$api = new Proxmox ('sincontrol', '***REMOVED***');
		$nodes = $api->getNodes ();
		
		return View::make ('staff.virtualisation.index', compact ('nodes'));
	}
}
