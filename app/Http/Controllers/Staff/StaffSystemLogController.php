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

class StaffSystemLogController extends Controller
{
	public function index ()
	{
		$logs = Log::orderBy ('created_at', 'desc')->paginate ();
		
		return view ('staff.system.log.index', compact ('logs'));
	}
	
	public function search ()
	{
		$userId = Input::get ('userId');
		
		$logs = Log::where ('user_id', $userId)->orderBy ('created_at', 'desc')->paginate ();
		
		return view ('staff.system.log.index', compact ('logs'));
	}
	
	public function show ($log)
	{
		$data = json_decode ($log->data, true);
		$hLevel = 1;
		
		return view ('staff.system.log.show', compact ('log', 'data', 'hLevel'));
	}
	
	public function laravel ()
	{
		$laravelRaw = file_get_contents ('../app/storage/logs/laravel.log');
		$laravelRaw = explode (PHP_EOL . '[', $laravelRaw);
		$laravel = array ();
		foreach ($laravelRaw as $i => $trace)
		{
			$boom = explode (PHP_EOL, $trace);
			$title = $boom[0];
			if (strpos ($title, '[') !== 0)
				$title = '[' . $title;

			$laravel[$title] = implode (PHP_EOL, array_splice ($boom, 1));
		}
		
		return view ('staff.system.log.laravel', compact ('laravel'));
	}
}