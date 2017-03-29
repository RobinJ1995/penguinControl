<?php

class ProblemSolverController extends BaseController
{
	public function start ($user = NULL)
	{
		if ($user == NULL)
			$user = Auth::user ();
		$userId = $user->id;
		
		return View::make ('problem-solver.start', compact ('userId'));
	}
	
	public function schedule ()
	{
		$user = User::find (Input::get ('userId'));
		if ($user == NULL)
			throw new Exception ('Opgegeven gebruiker bestaat niet');
		
		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_PROBLEM_SOLVER;
		$task->data = json_encode (array ('userId' => $user->id));
		$task->save ();
		
		return Response::json (array ('taskId' => $task->id));
	}
	
	public function result ()
	{
		$task = SystemTask::find (Input::get ('taskId'));
		
		return $task;
	}
	
	public function allDry ()
	{
		$data = array ();
		$now = ceil (time () / 60 / 60 / 24);
		
		$users = User::where ('expire', '>', $now)
			->orWhere ('expire', '-1')
			->whereHas
			(
				'UserInfo',
				function ($q)
				{
					$q->where ('validated', 1);
				}
			)->get ();
		
		foreach ($users as $user)
		{
			$problemSolver = new ProblemSolver ($user);
			$data[$user->userInfo->username] = $problemSolver->run (false);
		}
		
		if (Request::ajax ())
			return Response::json ($data);
		else
			return View::make ('problem-solver.allDry', compact ('data'))->with ('alerts', array (new Alert (count ($users) . ' gebruikers gescand op problemen', 'success')));
	}
}