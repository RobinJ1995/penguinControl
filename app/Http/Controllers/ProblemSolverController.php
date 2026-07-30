<?php

namespace App\Http\Controllers;

use App\Alert;
use App\ProblemSolver;
use App\Models\SystemTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class ProblemSolverController extends Controller
{
	/**
	 * The problem solver runs as root: it creates directories and chowns them to the user
	 * it was pointed at. Naming somebody else was unchecked, so any authenticated user
	 * could queue that against any account //
	 */
	private static function authoriseFor (User $user)
	{
		$actor = Auth::user ();

		if ($actor === NULL || (! $actor->isAdmin () && $actor->id !== $user->id))
			abort (403, 'You don\'t have access to the requested resource!');
	}

	public function start ($user = NULL)
	{
		if ($user == NULL)
			$user = Auth::user ();
		else
			self::authoriseFor ($user);
		$userId = $user->id;

		return view ('problem-solver.start', compact ('userId'));
	}

	public function schedule ()
	{
		$user = User::find (request ('userId'));
		if ($user == NULL)
			throw new \Exception ('User does not exist');

		self::authoriseFor ($user);

		$task = new SystemTask ();
		$task->type = SystemTask::TYPE_PROBLEM_SOLVER;
		$task->data = json_encode (array ('userId' => $user->id));
		$task->save ();
		
		return Response::json (array ('taskId' => $task->id));
	}
	
	public function result ()
	{
		$task = SystemTask::find (request ('taskId'));
		
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
			return response ()->json ($data);
		else
			return view ('problem-solver.allDry', compact ('data'))->with ('alerts', array (new Alert (count ($users) . ' users have been scanned for problems.', Alert::TYPE_SUCCESS)));
	}
}