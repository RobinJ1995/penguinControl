<?php

class ServiceApache
{
	const INIT = 'sysvinit';
	
	public function reload ()
	{
		$cmdFormat;
		
		switch (self::INIT)
		{
			case 'sysvinit': // Falls through //
			case 'upstart':
				$cmdFormat = 'service {:service:} {:cmd:}';
				break;
			case 'systemd':
				$cmdFormat = 'systemctl {:cmd:} {:service:}';
				break;
		}
		
		$cmd = str_replace ('{:service:}', 'apache2', $cmdFormat);
		$cmd = str_replace ('{:cmd:}', 'reload', $cmd);
		
		exec ($cmd . ' 2>&1', $output, $exitStatus);
		
		return array
		(
			'exitcode' => $exitStatus,
			'output' => implode (PHP_EOL, $output)
		);
	}
}
