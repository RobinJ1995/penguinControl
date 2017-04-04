<?php

namespace App;

class Alert
{
	public $message;
	public $type;
	public $close;
	
	const TYPE_SUCCESS = 'success';
	const TYPE_INFO = 'info';
	const TYPE_WARNING = 'warning';
	const TYPE_ALERT = 'alert';
	const TYPE_SECONDARY = 'secondary';
	
	public function __construct ($message, $type = '', $close = false)
	{
		$this->message = $message;
		$this->type = $type;
		$this->close = $close;
	}
	
	// Not much use having getters & setters in this kind of class; better to just make its members public //
	
	public function __toString ()
	{
		return (string) view ('part.alert')->with ('alert', $this);
	}
}
