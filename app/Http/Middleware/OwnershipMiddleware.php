<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OwnershipMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @param  string $resourceName
	 *
	 * @return mixed
	 */
	public function handle ($request, Closure $next, string $resourceName)
	{
		$resource = $request->route ()->parameter ($resourceName);
		
		$user = Auth::user ();
		if (! $user->isAdmin () && $user->uid !== $resource->uid)
			abort (403, 'You don\'t have access to the requested resource!');
		
		return $next ($request);
	}
}
