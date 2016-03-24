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
}