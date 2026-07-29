<?php

use App\Http\Middleware\FeatureEnabled;
use App\Http\Middleware\OwnershipMiddleware;
use App\Http\Middleware\PluginActionsMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\ResourceLockedMiddleware;
use App\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure (basePath: dirname (__DIR__))
	->withRouting
	(
		web: __DIR__ . '/../routes/web.php',
		commands: __DIR__ . '/../routes/console.php'
	)
	->withMiddleware
	(
		function (Middleware $middleware): void
		{
			// This app's own TrimStrings subclass exempts this app's password field names //
			$middleware->replace (\Illuminate\Foundation\Http\Middleware\TrimStrings::class, TrimStrings::class);

			// Plugin actions run after every response and may replace it // See App\Plugin //
			$middleware->append (PluginActionsMiddleware::class);

			$middleware->alias
			(
				[
					'guest' => RedirectIfAuthenticated::class,
					'owner' => OwnershipMiddleware::class,
					'locked' => ResourceLockedMiddleware::class,
					'feature_enabled' => FeatureEnabled::class
				]
			);
		}
	)
	->withExceptions
	(
		function (Exceptions $exceptions): void
		{
			$exceptions->dontReport (TokenMismatchException::class);
		}
	)
	->create ();
