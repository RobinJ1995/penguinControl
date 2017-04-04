<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffVirtualisationController extends Controller
{
	public function index ()
	{
		$api = new Proxmox ('sincontrol@pve', '');
		$nodes = $api->getNodes ();
		
		return view ('staff.virtualisation.index', compact ('nodes'));
	}
}
