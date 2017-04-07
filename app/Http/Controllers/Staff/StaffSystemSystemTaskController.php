<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\UserLog;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class StaffSystemSystemTaskController extends Controller
{
	public function index ()
	{
		$tasks = SystemTask::all ();
		
		return view ('staff.system.systemtask.index', compact ('tasks'));
	}
	
	public function create ()
	{
		return view ('staff.system.systemtask.create');
	}

	public function store ()
	{
		$validator = Validator::make
		(
			array
			(
				'Type' => Input::get ('type'),
				'Start' => Input::get ('start'),
				'Interval' => Input::get ('interval'),
				'Interval-eenheid' => Input::get ('interval_unit'),
				'Einde' => Input::get ('end')
			),
			array
			(
				'Type' => array ('required', 'in:apache_reload,nuke_expired_vhosts,calculate_disk_usage'),
				'Start' => array ('nullable', 'date'),
				'Interval' => array ('nullable', 'numeric', 'required_with:Einde', 'min:1', 'max:113529600000'),
				'Interval-eenheid' => array ('nullable', 'required_with:Interval', 'in:sec,min,hour,day,week'),
				'Einde' => array ('nullable', 'date')
			)
		);
		
		if ($validator->fails ())
			return Redirect::to ('/staff/system/systemtask/create')->withInput ()->withErrors ($validator);
		
		$task = new SystemTask ();
		$task->type = Input::get ('type');
		$task->start = (empty (Input::get ('start')) ? time () : strtotime (Input::get ('start')));
		$factor = 1;
		switch (Input::get ('interval_unit'))
		{
			case 'week': // Falls through //
				$factor *= 7;
			case 'day': // Falls through //
				$factor *= 24;
			case 'hour': // Falls through //
				$factor *= 60;
			case 'min': // Falls through //
				$factor *= 60;
		}
		if (! empty (Input::get ('interval')))
			$task->interval = Input::get ('interval') * $factor;
		$task->end = (empty (Input::get ('end')) ? NULL : strtotime (Input::get ('end')));
		$task->started = 0;
		
		$task->save ();
		
		Log::log ('Systeemtaak aangemaakt', NULL, $task);
		
		return Redirect::to ('/staff/system/systemtask')->with ('alerts', array (new Alert ('Opdracht toegevoegd', Alert::TYPE_SUCCESS)));
	}
	
	public function show ($task)
	{
		$data = json_decode ($task->data, true);
		$h1 = new HtmlString ($task->getTitle ());
		
		return view ('staff.system.systemtask.show', compact ('task', 'data', 'h1'));
	}
	
	public function remove ($task)
	{
		$task->delete ();
		
		Log::log ('Systeemtaak verwijderd', NULL, $task);
		
		return Redirect::to ('/staff/system/systemtask')->with ('alerts', array (new Alert ('Opdracht verwijderd', Alert::TYPE_SUCCESS)));
	}

}
