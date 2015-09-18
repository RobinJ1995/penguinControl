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
				. 'Messages the user got to see: ' . PHP_EOL
				. $strAlerts;
			    
			$mailSent = error_send_data ('Danger! Mayday! Error!', $message, $ex);
		}
		
		return View::make ('layout.error', compact ('ex', 'alerts', 'mailSent'));
	}
}
