<?php

class StaffAbuseController extends BaseController
{
	public function index ()
	{
		$abuses = AbuseMain::where ('status', '!=', 1)->get ();
		
		return View::make ('staff.user.abuse.index', compact ('abuses'));
	}

	public function multi ()
	{
		$files = Input::get ('abuses');
		$status = 0;
		$alerts = array ();
		
		switch (Input::get ('action'))
		{
			case 'whitelist':
				$status = 1;
				break;
			case 'blacklist':
				$status = -1;
				break;
			default:
				throw new Exception ('Onbekende actie');
				break;
		}
		
		foreach ($files as $file)
		{
			$abuse = AbuseMain::where ('file', $file)->first ();
			$abuse->status = $status;
			
			$abuse->save ();
			
			$alerts[] = new Alert ('Gemarkeerd als ' . ($status > 0 ? 'OK' : 'Misbruik') . ': ' . $file, 'success');
		}
		
		return Redirect::to ('/staff/user/abuse')->with ('alerts', $alerts);
	}
}