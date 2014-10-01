<?php

class StaffSystemTaskController extends BaseController
{
	public function index ()
	{
		$tasks = SystemTask::all ();
		
		return View::make ('staff.systemtask.index', compact ('tasks'));
	}
	
	public function create ()
	{
		return View::make ('staff.systemtask.create');
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Type' => Input::get ('type'),
				'Commando' => Input::get ('command'),
				'Start' => Input::get ('start'),
				'Interval' => Input::get ('interval'),
				'Interval-eenheid' => Input::get ('interval_unit'),
				'Einde' => Input::get ('end')
			),
			array
			(
				'Type' => array ('required', 'in:apache_reload,custom'),
				'Commando' => array ('required_if:type,custom'),
				'Start' => array ('date'),
				'Interval' => array ('numeric', 'required_with:Einde', 'min:1', 'max:113529600000'),
				'Interval-eenheid' => array ('required_with:Interval', 'in:sec,min,hour,day,week'),
				'Einde' => array ('date')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/systemtask/create')->withInput ()->withErrors ($validator);
		
		$task = new SystemTask ();
		$task->type = Input::get ('type');
		if (Input::get ('type') == 'custom')
		{
			$task->data = json_encode
			(
				array
				(
					'command' => Input::get ('command')
				)
			);
		}
		$task->start = (empty (Input::get ('start')) ? time () : strtotime (Input::get ('start')));
		$factor = 1;
		switch (Input::get ('interval_unit'))
		{
			case 'week': // Falls through //
				$factor *= 7;
			case 'day':
				$factor *= 24;
			case 'hour':
				$factor *= 60;
			case 'min':
				$factor *= 60;
		}
		if (! empty (Input::get ('interval')))
			$task->interval = Input::get ('interval') * $factor;
		$task->end = strtotime (Input::get ('end'));
		
		$task->save ();
		
		return Redirect::to ('/staff/systemtask')->with ('alerts', array (new Alert ('Opdracht toegevoegd', 'success')));
	}
	
	public function remove ($task)
	{
		$task->delete ();
		
		return Redirect::to ('/staff/systemtask')->with ('alerts', array (new Alert ('Opdracht verwijderd', 'success')));
	}

}
