<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

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
		
		$logs = Log::where ('user_id', $userId)->paginate ();
		
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