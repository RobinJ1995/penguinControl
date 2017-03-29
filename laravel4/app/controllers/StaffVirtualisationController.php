<?php

class StaffVirtualisationController extends BaseController
{
	public function index ()
	{
		$api = new Proxmox ('sincontrol@pve', 'W"rC4~eA\u;5m\S?jfbj-G6Xebb[A6qm9q:7rmw45+q*_jjz*kN@!W(XU2\$Nzmm');
		$nodes = $api->getNodes ();
		
		return View::make ('staff.virtualisation.index', compact ('nodes'));
	}
}
