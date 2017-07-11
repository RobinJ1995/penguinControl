<?php

namespace App\Http\Middleware;

use App\Plugin;
use Closure;

class PluginActionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $route = $request->route ();
        if ($route)
        {
            $action = substr ($route->getActionName (), strrpos($route->getActionName (), '\\') + 1);
            $responses = Plugin::executeAllActions ($request, $action, ...array_values ($route->parameters ()));

            foreach ($responses as $pluginResponse)
            {
                if ($pluginResponse !== NULL)
                    return $pluginResponse;
            }
        }

        return $response;
    }
}
