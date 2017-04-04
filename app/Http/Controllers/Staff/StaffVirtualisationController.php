<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class StaffVirtualisationController extends Controller
{
	public function index ()
	{
		$api = new Proxmox ('sincontrol@pve', '');
		$nodes = $api->getNodes ();
		
		return view ('staff.virtualisation.index', compact ('nodes'));
	}
}
