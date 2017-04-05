<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class ErrorController extends Controller
{
	public function show ()
	{
		$ex = Session::get ('ex');
		$alerts = Session::get ('alerts');
		$strAlerts = '';
		$mailSent = false;
		
		if (! (empty ($ex) && empty ($alerts)))
		{
			if (! empty ($alerts))
			{
				foreach ($alerts as $key => $alert)
					$strAlerts .= '[' . $key . '] ' . $alert->message . PHP_EOL;
			}
		}
		
		return view ('layout.error', compact ('ex', 'alerts', 'mailSent'));
	}
}
