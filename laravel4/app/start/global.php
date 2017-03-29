<?php

/*
  |--------------------------------------------------------------------------
  | Register The Laravel Class Loader
  |--------------------------------------------------------------------------
  |
  | In addition to using Composer, you may use the Laravel class loader to
  | load your controllers and models. This is useful for keeping all of
  | your classes in the "global" namespace without Composer updating.
  |
 */

ClassLoader::addDirectories (array (
    app_path () . '/commands',
    app_path () . '/controllers',
    app_path () . '/models',
    app_path () . '/database/seeds',
));

/*
  |--------------------------------------------------------------------------
  | Application Error Logger
  |--------------------------------------------------------------------------
  |
  | Here we will configure the error logger setup for the application which
  | is built on top of the wonderful Monolog library. By default we will
  | build a basic log file setup which creates a single file for logs.
  |
 */

Log::useFiles (storage_path () . '/logs/laravel.log');

require app_path () . '/errors.php';

/*
  |--------------------------------------------------------------------------
  | Maintenance Mode Handler
  |--------------------------------------------------------------------------
  |
  | The "down" Artisan command gives you the ability to put an application
  | into maintenance mode. Here, you will define what is displayed back
  | to the user if maintenance mode is in effect for the application.
  |
 */

App::down (function()
{
	return Response::make ("SINControl is momenteel offline voor onderhoud. Dit betekent waarschijnlijk gewoon dat er updates of bugfixes worden doorgevoerd aan het systeem, in welk geval de downtime vrij kort zal zijn.", 503);
});

/*
  |--------------------------------------------------------------------------
  | Require The Filters File
  |--------------------------------------------------------------------------
  |
  | Next we will load the filters file for the application. This gives us
  | a nice separate location to store our route and application filter
  | definitions instead of putting them all in the main routes file.
  |
 */

require app_path () . '/filters.php';

require app_path () . '/validators.php';
require app_path () . '/menu.php';