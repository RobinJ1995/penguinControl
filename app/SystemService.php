<?php

namespace App;

abstract class SystemService
{
	protected $name; //EXAMPLE// Web server //
	protected $serverName; //EXAMPLE// Xena //
	protected $software; //EXAMPLE// apache2 // Service name //
	protected $needsSudo = false; // Whether sudo must be prefixed to the command //
	
	const INIT = 'systemd';
	
	public function status ()
	{
		// `systemctl is-active` answers through its exit code. The old check read
		// sysvinit's " is running." wording, which systemd never prints //
		return $this->cmd ('is-active')['exitcode'] === 0;
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
		$exitCode = NULL;

		exec ($cmd . ' 2>&1', $output, $exitCode);
		$output = array_map ('trim', $output);

		if ($returnAsString)
			return implode (PHP_EOL, $output);
		else
			return array
			(
				'exitcode' => $exitCode,
				'command' => $cmd,
				'output' => $output
			);
	}
}
