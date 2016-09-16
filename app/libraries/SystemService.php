<?php

abstract class SystemService
{
	protected $name; //EXAMPLE// Web server //
	protected $serverName; //EXAMPLE// Xena //
	protected $software; //EXAMPLE// apache2 // Service-naam //
	protected $ssh; //EXAMPLE// squid // app/config/remote.php //
	protected $needsSudo = false; // Of sudo voor het commando moet worden gezet //
	
	const INIT = 'systemd';
	
	public function status ()
	{
		$output = $this->cmd ('status');
		$ok = substr ($output[0], -strlen(' is running')) === ' is running.';
		
		return $ok;
	}
	
	protected function cmd ($command, $returnAsString = false)
	{
		$cmdFormat = '';
		
		switch (self::INIT)
		{
			case 'sysvinit': // Falls through //
			case 'upstart':
				$cmdFormat = '{:sudo:}/usr/sbin/service {:service:} {:cmd:}';
				break;
			case 'systemd':
				$cmdFormat = '{:sudo:}systemctl {:cmd:} {:service:}';
				break;
		}
		
		$cmd = str_replace ('{:sudo:}', $this->needsSudo ? 'sudo ' : '', $cmdFormat);
		$cmd = str_replace ('{:service:}', escapeshellcmd ($this->software), $cmd);
		$cmd = str_replace ('{:cmd:}', escapeshellcmd ($command), $cmd);
		
		$output = array ();
		if (! empty ($this->ssh))
		{
			SSH::into ($this->ssh)->run (array ($cmd),
				function ($line)
				{
					$output[] = trim ($line);
				}
			);
		}
		else
		{
			exec ($cmd . ' 2>&1', $output, $exitCode);
			foreach ($output as $line)
				$line = trim ($line);
		}

		if ($returnAsString)
			return implode (PHP_EOL, $output);
		else
			return array
			(
				'exitcode' => $exitCode,
				'output' => $output
			);
	}
}
