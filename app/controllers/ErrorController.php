<?php

class ErrorController extends BaseController
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
					$strAlerts .= '[' . $key . '] ' . $alert->getMessage () . PHP_EOL;
			}

			$message = 'SINControl just crashed!' . PHP_EOL
				. 'Fix it, monkey! Fix it!' . PHP_EOL
				. PHP_EOL
				. $ex . PHP_EOL
				. PHP_EOL
				. 'Messages the user got to see: ' . PHP_EOL
				. $strAlerts;

			$headers = 'From: sin@sinners.be' . "\r\n" .
				   'Content-type: text/plain'. "\r\n" .
    				   'CC: r0446734@student.thomasmore.be' . "\r\n";

			$mailSent = mail ('sin@sinners.be', 'Danger! Mayday! Error!' , $message, $headers);
		}
		
		return View::make ('layout.error', compact ('ex', 'alerts', 'mailSent'));
	}
}
