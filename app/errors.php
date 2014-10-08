<?php
/*
  |--------------------------------------------------------------------------
  | Application Error Handler
  |--------------------------------------------------------------------------
  |
  | Here you may handle any errors that occur in your application, including
  | logging them or displaying custom views for specific errors. You may
  | even register several error handlers to handle different types of
  | exceptions. If nothing is returned, the default error view is
  | shown, which includes a detailed stack trace during debug.
  |
 */

App::error (function(Exception $exception, $code)
{
	Log::error ($exception);
	
	if ($code == 404)
		return Redirect::to ('/page/home')->with ('alerts', array (new Alert ('De opgevraagde pagina bestaat niet.', 'info')));
	
	return Redirect::to ('/error')->with ('ex', new SinException ($exception));
});