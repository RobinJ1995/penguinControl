<?php

class SinException // Een gewone Exception doorgeven met ->with () kan niet // Serialization of 'Closure' is not allowed //
{
	private $message;
	private $file;
	private $line;
	private $trace;
	
	function __construct (Exception $ex)
	{
		$this->message = $ex->getMessage ();
		$this->file = $ex->getFile ();
		$this->line = $ex->getLine ();
		$this->trace = $ex->getTraceAsString ();
	}
	
	function __toString ()
	{
		return '<pre>Bericht: ' . $this->message . PHP_EOL
		    . 'Bestand: ' . $this->file . PHP_EOL
		    . 'Regel: ' . $this->line . '</pre>'
		    . '<h3>Stack trace</h3>'
		    . '<pre>' . $this->trace . '</pre>';
	}
}