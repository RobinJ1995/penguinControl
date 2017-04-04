<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route Model Binding // http://laravel.com/docs/routing#route-model-binding //
Route::model ('vhost', '\App\Models\Vhost');
Route::model ('ftp', '\App\Models\Ftp');
Route::model ('mDomain', '\App\Models\MailDomain');
Route::model ('mUser', '\App\Models\MailUser');
Route::model ('mFwd', '\App\Models\MailForward');
Route::model ('user', '\App\Models\User');
Route::model ('userInfo', '\App\Models\UserInfo');
Route::model ('userLog', '\App\Models\UserLog');
Route::model ('group', '\App\Models\Group');
Route::model ('limit', '\App\Models\UserLimit');
Route::model ('systemTask', '\App\Models\SystemTask');
Route::model ('log', '\App\Models\Log');

Route::bind ('page',
	function ($value, $route)
	{
		$page = \App\Models\Page::where ('name', $value)->first ();
		
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
Route::pattern ('userLog', '[0-9]+');
Route::pattern ('group', '[0-9]+');
Route::pattern ('limit', '[0-9]+');
Route::pattern ('systemTask', '[0-9]+');
Route::pattern ('log', '[0-9]+');

Route::pattern ('order', '[a-zA-Z\_]+');

// Home //
Route::get ('/', 'HomeController@show');
Route::get ('/home', 'HomeController@show');

// Pagina's //
Route::get ('/page/{page}', 'PageController@show');

// Error //
Route::get ('/error', 'ErrorController@show');

// User // Public //
Route::get ('user/login', 'UserController@getLogin');
Route::post ('user/login', 'UserController@login');
Route::get ('user/{user}/expired/renew/{validationcode}', 'UserController@renew');
Route::get ('user/register', 'UserController@getRegister');
Route::post ('user/register', 'UserController@register');
Route::get ('user/amnesia', 'UserController@getAmnesia');
Route::post ('user/amnesia', 'UserController@amnesia');
Route::get ('user/{user}/amnesia/login/{logintoken}', 'UserController@loginWithToken');
Route::get ('user/{user}/expired', 'UserController@getExpired');
Route::post ('user/{user}/expired', 'UserController@expired');

// Afscherming van routes met Route Filters // http://laravel.com/docs/routing#route-filters //
// Filters worden gedefinieerd in app/filters.php //
Route::group
(
	array
	(
		'before' => 'user'
	),
	function ()
	{
		// User //
		Route::get ('user/start', 'UserController@start');
		Route::get ('user/edit', 'UserController@edit');
		Route::post ('user/edit', 'UserController@update');
		Route::get ('user/logout', 'UserController@logout');
		
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
		
		// Mail // Forward //
		Route::get ('mail/forward', 'MailForwardController@index');
		Route::get ('mail/forward/create', 'MailForwardController@create');
		Route::post ('mail/forward/create', 'MailForwardController@store');
		Route::get ('mail/forward/{mFwd}/edit', 'MailForwardController@edit');
		Route::post ('mail/forward/{mFwd}/edit', 'MailForwardController@update');
		Route::get ('mail/forward/{mFwd}/remove', 'MailForwardController@remove');
		
		// Databases // Databasebeheer via PHPMyAdmin //
		Route::get ('database', 'DatabaseController@show');
	}
);

Route::group
(
	array
	(
		'before' => 'staff',
		'namespace' => 'Staff'
	),
	function ()
	{
		// Problem solver //
		Route::get ('sudo-fix-problem/{user?}', 'ProblemSolverController@start');
		Route::get ('problem-solver/{user?}', 'ProblemSolverController@start');
		Route::get ('problem-solver/schedule', 'ProblemSolverController@schedule');
		Route::get ('problem-solver/result', 'ProblemSolverController@result');
		Route::get ('problem-solver/all/dry', 'ProblemSolverController@allDry');
		
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
		Route::get ('staff/user/user/{userInfo}/approve', 'StaffUserController@getApprove');
		Route::post ('staff/user/user/{userInfo}/approve', 'StaffUserController@approve');
		Route::get ('staff/user/user/{userInfo}/reject', 'StaffUserController@reject');
		Route::get ('staff/user/user/{user}/more', 'StaffUserController@more');
		Route::get ('staff/user/user/{user}/more/loginToken', 'StaffUserController@generateLoginToken');
		
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
		Route::get ('staff/user/group/{group}/remove', 'StaffGroupController@remove');
		
		// Staff // User // Abuse //
		Route::get ('staff/user/abuse', 'StaffAbuseController@index');
		Route::post ('staff/user/abuse/multi', 'StaffAbuseController@multi');
		
		// Staff // User // UserLog //
		Route::get ('staff/user/log', 'StaffUserLogController@index');
		Route::get ('staff/user/log/search', 'StaffUserLogController@search');
		Route::get ('staff/user/log/create', 'StaffUserLogController@create');
		Route::post ('staff/user/log/create', 'StaffUserLogController@store');
		Route::get ('staff/user/log/{userLog}/edit', 'StaffUserLogController@edit');
		Route::post ('staff/user/log/{userLog}/edit', 'StaffUserLogController@update');
		Route::get ('staff/user/log/{userLog}/remove', 'StaffUserLogController@remove');
		Route::post ('staff/user/log/edit/checked', 'StaffUserLogController@editChecked');
		Route::post ('staff/user/log/export', 'StaffUserLogController@export');
		
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
		
		// Staff // Mail // Forward //
		Route::get ('staff/mail/forward', 'StaffMailForwardController@index');
		Route::get ('staff/mail/forward/create', 'StaffMailForwardController@create');
		Route::post ('staff/mail/forward/create', 'StaffMailForwardController@store');
		Route::get ('staff/mail/forward/{mFwd}/edit', 'StaffMailForwardController@edit');
		Route::post ('staff/mail/forward/{mFwd}/edit', 'StaffMailForwardController@update');
		Route::get ('staff/mail/forward/{mFwd}/remove', 'StaffMailForwardController@remove');
		
		// Staff // Maintenance //
		Route::get ('staff/maintenance/vhost/generate', 'StaffMaintenanceController@generateVHosts');
		Route::get ('staff/maintenance/vhost/save/all', 'StaffMaintenanceController@saveAllVHosts');
		Route::get ('staff/maintenance/service/generate', 'StaffMaintenanceController@generateServiceData');
		Route::get ('staff/maintenance/system/check', 'StaffMaintenanceController@systemCheck');
		
		// Staff // Page //
		Route::get ('staff/page', 'StaffPageController@index');
		Route::get ('staff/page/create', 'StaffPageController@create');
		Route::post ('staff/page/create', 'StaffPageController@store');
		Route::get ('staff/page/{page}/edit', 'StaffPageController@edit');
		Route::post ('staff/page/{page}/edit', 'StaffPageController@update');
		Route::get ('staff/page/{page}/remove', 'StaffPageController@remove');
		
		// Staff // System //
		Route::get ('staff/system/phpinfo', 'StaffSystemController@phpinfo');
		
		// Staff // System // Log //
		Route::get ('staff/system/log', 'StaffSystemLogController@index');
		Route::get ('staff/system/log/search', 'StaffSystemLogController@search');
		Route::get ('staff/system/log/{log}/show', 'StaffSystemLogController@show');
		Route::get ('staff/system/log/laravel', 'StaffSystemLogController@laravel');
		
		// Staff // System // SystemTask //
		Route::get ('staff/system/systemtask', 'StaffSystemSystemTaskController@index');
		Route::get ('staff/system/systemtask/create', 'StaffSystemSystemTaskController@create');
		Route::post ('staff/system/systemtask/create', 'StaffSystemSystemTaskController@store');
		Route::get ('staff/system/systemtask/{systemTask}/show', 'StaffSystemSystemTaskController@show');
		Route::get ('staff/system/systemtask/{systemTask}/remove', 'StaffSystemSystemTaskController@remove');
		
		// Staff // Virtualisation //
		Route::get ('staff/virtualisation', 'StaffVirtualisationController@index');
	}
);