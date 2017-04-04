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

class StaffSystemController extends Controller
{
	public function phpinfo ()
	{
		ob_start ();
		phpinfo ();
		$html = ob_get_contents ();
		ob_end_clean ();
		$info = NULL;
		
		preg_match ('#<body([^>]+)?>(.+)<\/body>#ims', $html, $matches);
		if (isset ($matches[2]))
			$info = $matches[2];
		else
			$info = $html; // The layout will get messed up but at least the information still gets displayed //
		
		return view ('staff.system.phpinfo', compact ('info'));
	}
}