<?php

namespace App\Http\Middleware;

use Closure;

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
	public function handle ($request, Closure $next, string $featureName)
	{
		if (! is_feature_enabled ($featureName))
			abort (404, 'The requested feature is disabled.');
		
		return $next ($request);
	}
}
