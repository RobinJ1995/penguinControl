<?php

class SystemTask
{
	private $properties = array ();
	
	public static function get ($db)
	{
		$q = $db->prepare
		(
			'SELECT *
			FROM system_task
			WHERE start <= :now
			AND
			(
				`end` >= :now
				OR `end` IS NULL
			)
			AND `started` = 0;'
		);
		$q->bindValue (':now', time ());
		$q->execute ();
		
		$r = $q->fetchAll ();
		
		$tasks = array ();
		foreach ($r as $task)
			$tasks[] = new SystemTask ($task['id'], $task['type'], $task['data'], $task['start'], $task['end'], $task['interval'], $task['exitcode'], $task['started']);
		
		return $tasks;
	}
	
	public function __construct ($id, $type, $data, $start, $end, $interval, $exitcode, $started)
	{
		$this->id = $id;
		$this->type = $type;
		$this->data = $data;
		$this->start = $start;
		$this->end = $end;
		$this->interval = $interval;
		$this->exitcode = $exitcode;
		$this->started = $started;
	}
	
	public function __get ($property)
	{
		return $this->properties[$property];
	}
	
	public function __set ($property, $value)
	{
		$this->properties[$property] = $value;
	}
	
	public function save ($db)
	{
		$q = $db->prepare
		(
			'UPDATE system_task
			SET `type` = :type, `data` = :data, `start` = :start, `end` = :end, `interval` = :interval, `exitcode` = :exitcode, `started` = :started
			WHERE id = :id;'
		);
		$q->bindValue (':type', $this->type);
		$q->bindValue (':data', $this->data);
		$q->bindValue (':start', $this->start);
		$q->bindValue (':end', $this->end);
		$q->bindValue (':interval', $this->interval);
		$q->bindValue (':exitcode', $this->exitcode);
		$q->bindValue (':started', $this->started);
		$q->bindValue (':id', $this->id);
		
		$q->execute ();
	}
}