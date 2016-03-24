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
	
	public function scan ()
	{
		$user = User::find (Input::get ('userId'));
		
		$problemSolver = new ProblemSolver ($user);
		$data = $problemSolver->run ();
		
		return Response::json ($data);
	}
}