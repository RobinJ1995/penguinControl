<?php

class Alert
{
	private $message;
	private $type;
	private $close;

	public function __construct ($message, $type = '', $close = false)
	{
		$this->message = $message;
		$this->type = $type;
		$this->close = $close;
	}

	public function getMessage ()
	{
		return $this->message;
	}

	public function getType ()
	{
		return $this->type;
	}

	public function setMessage ($message)
	{
		$this->message = $message;
	}

	public function setType ($type)
	{
		$this->type = $type;
	}

	public function getClose ()
	{
		return $this->close;
	}

	public function setClose ($close)
	{
		$this->close = $close;
	}

}
