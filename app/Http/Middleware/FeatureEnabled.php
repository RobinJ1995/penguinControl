<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class FeatureEnabled
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @param  string $featureName
	 *
	 * @return mixed
	 */
	public function handle ($request, Closure $next, $featureName)
	{
		if (! Config::get ('penguin.' . $featureName, false))
			abort (404, 'The requested feature is disabled.');
		
		return $next ($request);
	}
}
