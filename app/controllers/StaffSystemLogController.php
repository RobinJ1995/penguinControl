<?php

class StaffSystemLogController extends BaseController
{
	public function index ()
	{
		$logs = SinLog::orderBy ('created_at', 'desc')->paginate ();
		
		return View::make ('staff.system.log.index', compact ('logs'));
	}
}