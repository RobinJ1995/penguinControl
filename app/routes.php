<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

// Afscherming van routes met Route Filters // http://laravel.com/docs/routing#route-filters //
// Filters worden gedefinieerd in app/filters.php //
Route::when ('staff/*', 'staff');

// Route Model Binding // http://laravel.com/docs/routing#route-model-binding //
Route::model ('vhost', 'ApacheVhostVirtual');
Route::model ('ftp', 'FtpUserVirtual');
Route::model ('mDomain', 'MailDomainVirtual');
Route::model ('mUser', 'MailUserVirtual');
Route::model ('mFwd', 'MailForwardingVirtual');

Route::model ('user', 'User');
Route::model ('userInfo', 'UserInfo');
Route::model ('group', 'Group');
Route::model ('limit', 'UserLimit');
Route::model ('systemTask', 'SystemTask');

Route::bind ('page',
	function ($value, $route)
	{
		$page = Page::where ('name', $value)->first ();
		
		if (empty ($page))
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ();
		
		return $page;
	}
);

// Route Constraint Pattern // http://laravel.com/docs/routing#route-parameters //
Route::pattern ('vhost', '[0-9]+');
Route::pattern ('ftp', '[0-9]+');
Route::pattern ('mDomain', '[0-9]+');
Route::pattern ('mUser', '[0-9]+');
Route::pattern ('mFwd', '[0-9]+');

Route::pattern ('user', '[0-9]+');
Route::pattern ('userInfo', '[0-9]+');
Route::pattern ('group', '[0-9]+');
Route::pattern ('limit', '[0-9]+');
Route::pattern ('systemTask', '[0-9]+');

Route::pattern ('order', '[a-zA-Z\_]+');

// Home //
Route::get ('/', 'HomeController@show');
Route::get ('/home', 'HomeController@show');

// Pagina's //
Route::get ('/page/{page}', 'PageController@show');

// Error //
Route::get ('/error', 'ErrorController@show');

// User //
Route::get ('user/start', 'UserController@start');
Route::get ('user/login', 'UserController@getLogin');
Route::post ('user/login', 'UserController@login');
Route::get ('user/{user}/expired', 'UserController@getExpired');
Route::post ('user/{user}/expired', 'UserController@expired');
Route::get ('user/{user}/expired/renew/{validationcode}', 'UserController@renew');
Route::get ('user/register', 'UserController@getRegister');
Route::post ('user/register', 'UserController@register');
Route::get ('user/edit', 'UserController@edit');
Route::post ('user/edit', 'UserController@update');
Route::get ('user/logout', 'UserController@logout');
Route::get ('user/amnesia', 'UserController@getAmnesia');
Route::post ('user/amnesia', 'UserController@amnesia');
Route::get ('user/{user}/amnesia/login/{logintoken}', 'UserController@loginWithToken');

// vHost //
Route::get ('website/vhost', 'VHostController@index');
Route::get ('website/vhost/create', 'VHostController@create');
Route::post ('website/vhost/create', 'VHostController@store');
Route::get ('website/vhost/{vhost}/edit', 'VHostController@edit');
Route::post ('website/vhost/{vhost}/edit', 'VHostController@update');
Route::get ('website/vhost/{vhost}/remove', 'VHostController@remove');

// FTP //
Route::get ('ftp', 'FtpController@index');
Route::get ('ftp/create', 'FtpController@create');
Route::post ('ftp/create', 'FtpController@store');
Route::get ('ftp/{ftp}/edit', 'FtpController@edit');
Route::post ('ftp/{ftp}/edit', 'FtpController@update');
Route::get ('ftp/{ftp}/remove', 'FtpController@remove');

// Mail // Algemeen //
Route::get ('mail', 'MailController@show');
Route::post ('mail', 'MailController@update');

// Mail // Domain //
Route::get ('mail/domain', 'MailDomainController@index');
Route::get ('mail/domain/create', 'MailDomainController@create');
Route::post ('mail/domain/create', 'MailDomainController@store');
Route::get ('mail/domain/{mDomain}/edit', 'MailDomainController@edit');
Route::post ('mail/domain/{mDomain}/edit', 'MailDomainController@update');
Route::get ('mail/domain/{mDomain}/remove', 'MailDomainController@remove');

// Mail // User //
Route::get ('mail/user', 'MailUserController@index');
Route::get ('mail/user/create', 'MailUserController@create');
Route::post ('mail/user/create', 'MailUserController@store');
Route::get ('mail/user/{mUser}/edit', 'MailUserController@edit');
Route::post ('mail/user/{mUser}/edit', 'MailUserController@update');
Route::get ('mail/user/{mUser}/remove', 'MailUserController@remove');

// Mail // Forwarding //
Route::get ('mail/forwarding', 'MailForwardingController@index');
Route::get ('mail/forwarding/create', 'MailForwardingController@create');
Route::post ('mail/forwarding/create', 'MailForwardingController@store');
Route::get ('mail/forwarding/{mFwd}/edit', 'MailForwardingController@edit');
Route::post ('mail/forwarding/{mFwd}/edit', 'MailForwardingController@update');
Route::get ('mail/forwarding/{mFwd}/remove', 'MailForwardingController@remove');

// Databases // Databasebeheer via PHPMyAdmin //
Route::get ('database', 'DatabaseController@show');

// Staff // User // User //
Route::get ('staff/user/user', 'StaffUserController@index');
Route::get ('staff/user/user/search', 'StaffUserController@search');
Route::get ('staff/user/user/order/{order}', 'StaffUserController@index');
Route::get ('staff/user/user/create', 'StaffUserController@create');
Route::post ('staff/user/user/create', 'StaffUserController@store');
Route::get ('staff/user/user/{user}/edit', 'StaffUserController@edit');
Route::post ('staff/user/user/{user}/edit', 'StaffUserController@update');
Route::get ('staff/user/user/{user}/remove', 'StaffUserController@remove');
Route::get ('staff/user/user/{user}/login', 'StaffUserController@login');
Route::get ('staff/user/user/{user}/expire', 'StaffUserController@getExpire');
Route::post ('staff/user/user/{user}/expire', 'StaffUserController@expire');
Route::get ('staff/user/user/{userInfo}/validate', 'StaffUserController@getValidate');
Route::post ('staff/user/user/{userInfo}/validate', 'StaffUserController@validate');
Route::get ('staff/user/user/{userInfo}/reject', 'StaffUserController@reject');

// Staff // User // Limit //
Route::get ('staff/user/limit', 'StaffUserLimitController@index');
Route::get ('staff/user/limit/order/{order}', 'StaffUserLimitController@index');
Route::get ('staff/user/limit/create', 'StaffUserLimitController@create');
Route::post ('staff/user/limit/create', 'StaffUserLimitController@store');
Route::get ('staff/user/limit/{limit}/edit', 'StaffUserLimitController@edit');
Route::post ('staff/user/limit/{limit}/edit', 'StaffUserLimitController@update');
Route::get ('staff/user/limit/{limit}/remove', 'StaffUserLimitController@remove');

// Staff // User // Group //
Route::get ('staff/user/group', 'StaffGroupController@index');
Route::get ('staff/user/group/create', 'StaffGroupController@create');
Route::post ('staff/user/group/create', 'StaffGroupController@store');
Route::get ('staff/user/group/{user}/remove', 'StaffGroupController@remove');

// Staff // User // Abuse //
Route::get ('staff/user/abuse', 'StaffAbuseController@index');
Route::post ('staff/user/abuse/multi', 'StaffAbuseController@multi');

// Staff // User // UserLog //
Route::get ('staff/user/log', 'StaffUserLogController@index');
Route::get ('staff/user/log/search', 'StaffUserLogController@search');
Route::get ('staff/user/log/create', 'StaffUserLogController@create');
Route::post ('staff/user/log/create', 'StaffUserLogController@store');
Route::get ('staff/user/log/{userlog}/edit', 'StaffUserLogController@edit');
Route::post ('staff/user/log/{userlog}/edit', 'StaffUserLogController@update');
Route::get ('staff/user/log/{userlog}/remove', 'StaffUserLogController@remove');

// Staff // Website // vHost //
Route::get ('staff/website/vhost', 'StaffVHostController@index');
Route::get ('staff/website/vhost/search', 'StaffVHostController@search');
Route::get ('staff/website/vhost/create', 'StaffVHostController@create');
Route::post ('staff/website/vhost/create', 'StaffVHostController@store');
Route::get ('staff/website/vhost/{vhost}/edit', 'StaffVHostController@edit');
Route::post ('staff/website/vhost/{vhost}/edit', 'StaffVHostController@update');
Route::get ('staff/website/vhost/{vhost}/remove', 'StaffVHostController@remove');

// Staff // FTP //
Route::get ('staff/ftp', 'StaffFtpController@index');
Route::get ('staff/ftp/search', 'StaffFtpController@search');
Route::get ('staff/ftp/create', 'StaffFtpController@create');
Route::post ('staff/ftp/create', 'StaffFtpController@store');
Route::get ('staff/ftp/{ftp}/edit', 'StaffFtpController@edit');
Route::post ('staff/ftp/{ftp}/edit', 'StaffFtpController@update');
Route::get ('staff/ftp/{ftp}/remove', 'StaffFtpController@remove');

// Staff // Mail // Domain //
Route::get ('staff/mail/domain', 'StaffMailDomainController@index');
Route::get ('staff/mail/domain/create', 'StaffMailDomainController@create');
Route::post ('staff/mail/domain/create', 'StaffMailDomainController@store');
Route::get ('staff/mail/domain/{mDomain}/edit', 'StaffMailDomainController@edit');
Route::post ('staff/mail/domain/{mDomain}/edit', 'StaffMailDomainController@update');
Route::get ('staff/mail/domain/{mDomain}/remove', 'StaffMailDomainController@remove');

// Staff // Mail //
Route::get ('staff/mail/search', 'StaffMailController@search');

// Staff // Mail // User //
Route::get ('staff/mail/user', 'StaffMailUserController@index');
Route::get ('staff/mail/user/create', 'StaffMailUserController@create');
Route::post ('staff/mail/user/create', 'StaffMailUserController@store');
Route::get ('staff/mail/user/{mUser}/edit', 'StaffMailUserController@edit');
Route::post ('staff/mail/user/{mUser}/edit', 'StaffMailUserController@update');
Route::get ('staff/mail/user/{mUser}/remove', 'StaffMailUserController@remove');

// Staff // Mail // Forwarding //
Route::get ('staff/mail/forwarding', 'StaffMailForwardingController@index');
Route::get ('staff/mail/forwarding/create', 'StaffMailForwardingController@create');
Route::post ('staff/mail/forwarding/create', 'StaffMailForwardingController@store');
Route::get ('staff/mail/forwarding/{mFwd}/edit', 'StaffMailForwardingController@edit');
Route::post ('staff/mail/forwarding/{mFwd}/edit', 'StaffMailForwardingController@update');
Route::get ('staff/mail/forwarding/{mFwd}/remove', 'StaffMailForwardingController@remove');

// Staff // Maintenance //
Route::get ('staff/maintenance/vhost/generate', 'StaffMaintenanceController@generateVHosts');
Route::get ('staff/maintenance/vhost/save/all', 'StaffMaintenanceController@saveAllVHosts');
Route::get ('staff/maintenance/service/generate', 'StaffMaintenanceController@generateServiceData');

// Staff // Page //
Route::get ('staff/page', 'StaffPageController@index');
Route::get ('staff/page/create', 'StaffPageController@create');
Route::post ('staff/page/create', 'StaffPageController@store');
Route::get ('staff/page/{page}/edit', 'StaffPageController@edit');
Route::post ('staff/page/{page}/edit', 'StaffPageController@update');
Route::get ('staff/page/{page}/remove', 'StaffPageController@remove');

// Staff // SystemTask //
Route::get ('staff/systemtask', 'StaffSystemTaskController@index');
Route::get ('staff/systemtask/create', 'StaffSystemTaskController@create');
Route::post ('staff/systemtask/create', 'StaffSystemTaskController@store');
Route::get ('staff/systemtask/{systemTask}/remove', 'StaffSystemTaskController@remove');
