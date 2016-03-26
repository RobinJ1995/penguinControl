<?php

class StaffSystemController extends BaseController
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
		
		return View::make ('staff.system.phpinfo', compact ('info'));
	}
}