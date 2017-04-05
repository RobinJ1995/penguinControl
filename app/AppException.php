<?php

namespace App;

class AppException // Een gewone Exception doorgeven met ->with () kan niet // Serialization of 'Closure' is not allowed //
{
	private $message;
	private $file;
	private $line;
	private $trace;
	
	function __construct (\Exception $ex)
	{
		$this->message = $ex->getMessage ();
		$this->file = $ex->getFile ();
		$this->line = $ex->getLine ();
		$this->trace = $ex->getTraceAsString ();
	}
	
	function __toString ()
	{
		return '<pre>Message: ' . $this->message . PHP_EOL
		    . 'File: ' . $this->file . PHP_EOL
		    . 'Line: ' . $this->line . '</pre>'
		    . '<h3>Stack trace</h3>'
		    . '<pre>' . $this->trace . '</pre>';
	}
}