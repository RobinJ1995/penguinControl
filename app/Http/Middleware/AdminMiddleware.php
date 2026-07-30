<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/*
 * Gates the staff area.
 *
 * Until this existed, every route under `staff/` was protected by `auth` alone, and
 * `is_admin()` was consulted only to decide which menu entries to draw. Any authenticated
 * user who typed the URL could reach them -- including
 * `staff/user/user/{user}/login`, which logs you in as the user you name.
 */
class AdminMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 *
	 * @return mixed
	 */
	public function handle ($request, Closure $next)
	{
		$user = Auth::user ();

		if ($user === NULL || ! $user->isAdmin ())
			abort (403, 'This page is restricted to administrators.');

		return $next ($request);
	}
}
