<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ResourceLockedMiddleware
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
	public function handle ($request, Closure $next, $resourceName)
	{
		$resource = $request->route ()->parameter ($resourceName);
		
		$user = Auth::user ();
		if (! $user->isAdmin () && $resource->locked)
			abort (403, 'The requested resource has been locked!');
		
		return $next ($request);
	}
}
