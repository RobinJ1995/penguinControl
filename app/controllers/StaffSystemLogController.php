<?php

class StaffSystemLogController extends BaseController
{
	public function index ()
	{
		$logs = SinLog::orderBy ('created_at', 'desc')->paginate ();
		
		return View::make ('staff.system.log.index', compact ('logs'));
	}
	
	public function search ()
	{
		$userId = Input::get ('userId');
		
		$logs = SinLog::where ('user_id', $userId)->paginate ();
		
		return View::make ('staff.system.log.index', compact ('logs'));
	}
	
	public function show ($log)
	{
		$data = json_decode ($log->data, true);
		$hLevel = 1;
		
		return View::make ('staff.system.log.show', compact ('log', 'data', 'hLevel'));
	}
}