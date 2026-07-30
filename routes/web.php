<?php

use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FtpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MailDomainController;
use App\Http\Controllers\MailForwardController;
use App\Http\Controllers\MailUserController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProblemSolverController;
use App\Http\Controllers\Staff\StaffFtpController;
use App\Http\Controllers\Staff\StaffGroupController;
use App\Http\Controllers\Staff\StaffMailController;
use App\Http\Controllers\Staff\StaffMailDomainController;
use App\Http\Controllers\Staff\StaffMailForwardController;
use App\Http\Controllers\Staff\StaffMailUserController;
use App\Http\Controllers\Staff\StaffMaintenanceController;
use App\Http\Controllers\Staff\StaffPageController;
use App\Http\Controllers\Staff\StaffSystemController;
use App\Http\Controllers\Staff\StaffSystemLogController;
use App\Http\Controllers\Staff\StaffSystemSystemTaskController;
use App\Http\Controllers\Staff\StaffUserController;
use App\Http\Controllers\Staff\StaffUserLimitController;
use App\Http\Controllers\Staff\StaffVHostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VHostController;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\Vhost;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are all assigned the "web" middleware group by the framework.
| Now create something great!
|
*/

// Route Model Binding // https://laravel.com/docs/routing#route-model-binding //
Route::model ('vhost', Vhost::class);
Route::model ('ftp', Ftp::class);
Route::model ('mDomain', MailDomain::class);
Route::model ('mUser', MailUser::class);
Route::model ('mFwd', MailForward::class);
Route::model ('user', User::class);
Route::model ('userInfo', UserInfo::class);
Route::model ('group', Group::class);
Route::model ('limit', UserLimit::class);
Route::model ('systemTask', SystemTask::class);
Route::model ('log', Log::class);

Route::bind ('page',
	function ($value, $route)
	{
		$page = Page::where ('name', $value)->first ();

		if (empty ($page))
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ();

		return $page;
	}
);

// Route Constraint Pattern // https://laravel.com/docs/routing#parameters-regular-expression-constraints //
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
Route::pattern ('log', '[0-9]+');

Route::pattern ('order', '[a-zA-Z\_]+');

// Home //
Route::get ('/', [HomeController::class, 'show'])->name ('root');
Route::get ('/home', [HomeController::class, 'show'])->name ('home');

// Pages //
if (config ('penguin.website', false))
	Route::get ('/page/{page}', [PageController::class, 'show'])->name ('page.show');

// Error //
Route::get ('/error', [ErrorController::class, 'show'])->name ('error');

// User // Public //
Route::get ('user/login', [UserController::class, 'getLogin'])->name ('user.login');
Route::post ('user/login', [UserController::class, 'login'])->name ('user.login.submit');
Route::get ('user/amnesia', [UserController::class, 'getAmnesia'])->name ('user.amnesia');
Route::post ('user/amnesia', [UserController::class, 'amnesia'])->name ('user.amnesia.submit');
Route::get ('user/{user}/amnesia/login/{logintoken}', [UserController::class, 'loginWithToken'])->name ('user.amnesia.login');
Route::get ('user/{user}/expired', [UserController::class, 'getExpired'])->name ('user.expired');
if (config ('penguin.user_registration', false))
{
	Route::get ('user/register', [UserController::class, 'getRegister'])->name ('user.register');
	Route::post ('user/register', [UserController::class, 'register'])->name ('user.register.submit');
}

// Protecting routes with route middleware // https://laravel.com/docs/middleware //
Route::group
(
	[
		'middleware' => 'auth'
	],
	function ()
	{
		// User //
		Route::get ('user/start', [UserController::class, 'start'])->name ('user.start');
		Route::get ('user/edit', [UserController::class, 'edit'])->name ('user.edit');
		Route::post ('user/edit', [UserController::class, 'update'])->name ('user.update');
		Route::get ('user/logout', [UserController::class, 'logout'])->name ('user.logout');

		// vHost //
		Route::group
		(
			['middleware' => ['owner:vhost', 'feature_enabled:vhost', 'locked:vhost']],
			function ()
			{
				Route::get ('website/vhost', [VHostController::class, 'index'])->name ('vhost.index');
				Route::get ('website/vhost/create', [VHostController::class, 'create'])->name ('vhost.create');
				Route::post ('website/vhost/create', [VHostController::class, 'store'])->name ('vhost.store');
				Route::get ('website/vhost/{vhost}/edit', [VHostController::class, 'edit'])->name ('vhost.edit');
				Route::post ('website/vhost/{vhost}/edit', [VHostController::class, 'update'])->name ('vhost.update');
				Route::get ('website/vhost/{vhost}/remove', [VHostController::class, 'remove'])->name ('vhost.remove');
			}
		);

		// FTP //
		Route::group
		(
			['middleware' => ['owner:ftp', 'feature_enabled:ftp', 'locked:ftp']],
			function ()
			{
				Route::get ('ftp', [FtpController::class, 'index'])->name ('ftp.index');
				Route::get ('ftp/create', [FtpController::class, 'create'])->name ('ftp.create');
				Route::post ('ftp/create', [FtpController::class, 'store'])->name ('ftp.store');
				Route::get ('ftp/{ftp}/edit', [FtpController::class, 'edit'])->name ('ftp.edit');
				Route::post ('ftp/{ftp}/edit', [FtpController::class, 'update'])->name ('ftp.update');
				Route::get ('ftp/{ftp}/remove', [FtpController::class, 'remove'])->name ('ftp.remove');
			}
		);

		// Mail // General //
		Route::group
		(
			['middleware' => ['feature_enabled:mail']],
			function ()
			{
				Route::get ('mail', [MailController::class, 'show'])->name ('mail.show');
				Route::post ('mail', [MailController::class, 'update'])->name ('mail.update');

				// Mail // Domain //
				Route::group
				(
					['middleware' => ['owner:mDomain', 'locked:mDomain']],
					function ()
					{
						Route::get ('mail/domain', [MailDomainController::class, 'index'])->name ('mail.domain.index');
						Route::get ('mail/domain/create', [MailDomainController::class, 'create'])->name ('mail.domain.create');
						Route::post ('mail/domain/create', [MailDomainController::class, 'store'])->name ('mail.domain.store');
						Route::get ('mail/domain/{mDomain}/edit', [MailDomainController::class, 'edit'])->name ('mail.domain.edit');
						Route::post ('mail/domain/{mDomain}/edit', [MailDomainController::class, 'update'])->name ('mail.domain.update');
						Route::get ('mail/domain/{mDomain}/remove', [MailDomainController::class, 'remove'])->name ('mail.domain.remove');
					}
				);

				// Mail // User //
				Route::group
				(
					['middleware' => ['owner:mUser', 'feature_enabled:mail_user', 'locked:mUser']],
					function ()
					{
						Route::get ('mail/user', [MailUserController::class, 'index'])->name ('mail.user.index');
						Route::get ('mail/user/create', [MailUserController::class, 'create'])->name ('mail.user.create');
						Route::post ('mail/user/create', [MailUserController::class, 'store'])->name ('mail.user.store');
						Route::get ('mail/user/{mUser}/edit', [MailUserController::class, 'edit'])->name ('mail.user.edit');
						Route::post ('mail/user/{mUser}/edit', [MailUserController::class, 'update'])->name ('mail.user.update');
						Route::get ('mail/user/{mUser}/remove', [MailUserController::class, 'remove'])->name ('mail.user.remove');
					}
				);

				// Mail // Forward //
				Route::group
				(
					['middleware' => ['owner:mFwd', 'feature_enabled:mail_forward', 'locked:mFwd']],
					function ()
					{
						Route::get ('mail/forward', [MailForwardController::class, 'index'])->name ('mail.forward.index');
						Route::get ('mail/forward/create', [MailForwardController::class, 'create'])->name ('mail.forward.create');
						Route::post ('mail/forward/create', [MailForwardController::class, 'store'])->name ('mail.forward.store');
						Route::get ('mail/forward/{mFwd}/edit', [MailForwardController::class, 'edit'])->name ('mail.forward.edit');
						Route::post ('mail/forward/{mFwd}/edit', [MailForwardController::class, 'update'])->name ('mail.forward.update');
						Route::get ('mail/forward/{mFwd}/remove', [MailForwardController::class, 'remove'])->name ('mail.forward.remove');
					}
				);
			}
		);

		// Databases // Database management via PHPMyAdmin //
		Route::group
		(
			['middleware' => ['feature_enabled:database']],
			function ()
			{
				Route::get ('database', [DatabaseController::class, 'show'])->name ('database.show');
			}
		);

		Route::get ('system/systemtask/{systemTask}/show', [StaffSystemSystemTaskController::class, 'show'])->name ('systemtask.show');
	}
);

Route::group
(
	array
	(
		'middleware' => 'auth'
	),
	function ()
	{
		// Problem solver //
		Route::get ('sudo-fix-problem/{user?}', [ProblemSolverController::class, 'start'])->name ('problem-solver.start.alias');
		Route::get ('problem-solver/{user?}', [ProblemSolverController::class, 'start'])->name ('problem-solver.start');
		Route::get ('problem-solver/schedule', [ProblemSolverController::class, 'schedule'])->name ('problem-solver.schedule');
		Route::get ('problem-solver/result', [ProblemSolverController::class, 'result'])->name ('problem-solver.result');
	}
);

// Everything below is the staff area. It is gated on `admin` as well as `auth`: without
// that, any authenticated user could reach staff/user/user/{user}/login and be logged in
// as whoever they named. is_admin() is used in the views to decide what to draw, which is
// not authorisation //
Route::group
(
	array
	(
		'middleware' => array ('auth', 'admin')
	),
	function ()
	{
		// Problem solver // Scans every user, so it is staff-only //
		Route::get ('problem-solver/all/dry', [ProblemSolverController::class, 'allDry'])->name ('problem-solver.all-dry');

		// Staff // User // User //
		Route::get ('staff/user/user', [StaffUserController::class, 'index'])->name ('staff.user.index');
		Route::get ('staff/user/user/search', [StaffUserController::class, 'search'])->name ('staff.user.search');
		Route::get ('staff/user/user/order/{order}', [StaffUserController::class, 'index'])->name ('staff.user.index.ordered');
		Route::get ('staff/user/user/create', [StaffUserController::class, 'create'])->name ('staff.user.create');
		Route::post ('staff/user/user/create', [StaffUserController::class, 'store'])->name ('staff.user.store');
		Route::get ('staff/user/user/{user}/edit', [StaffUserController::class, 'edit'])->name ('staff.user.edit');
		Route::post ('staff/user/user/{user}/edit', [StaffUserController::class, 'update'])->name ('staff.user.update');
		Route::get ('staff/user/user/{user}/remove', [StaffUserController::class, 'remove'])->name ('staff.user.remove');
		Route::get ('staff/user/user/{user}/login', [StaffUserController::class, 'login'])->name ('staff.user.login');
		Route::get ('staff/user/user/{user}/expire', [StaffUserController::class, 'getExpire'])->name ('staff.user.expire');
		Route::post ('staff/user/user/{user}/expire', [StaffUserController::class, 'expire'])->name ('staff.user.expire.submit');
		Route::get ('staff/user/user/{userInfo}/approve', [StaffUserController::class, 'getApprove'])->name ('staff.user.approve');
		Route::post ('staff/user/user/{userInfo}/approve', [StaffUserController::class, 'approve'])->name ('staff.user.approve.submit');
		Route::get ('staff/user/user/{userInfo}/reject', [StaffUserController::class, 'reject'])->name ('staff.user.reject');
		Route::get ('staff/user/user/{user}/more', [StaffUserController::class, 'more'])->name ('staff.user.more');
		Route::get ('staff/user/user/{user}/more/loginToken', [StaffUserController::class, 'generateLoginToken'])->name ('staff.user.login-token');

		// Staff // User // Limit //
		Route::get ('staff/user/limit', [StaffUserLimitController::class, 'index'])->name ('staff.limit.index');
		Route::get ('staff/user/limit/order/{order}', [StaffUserLimitController::class, 'index'])->name ('staff.limit.index.ordered');
		Route::get ('staff/user/limit/create', [StaffUserLimitController::class, 'create'])->name ('staff.limit.create');
		Route::post ('staff/user/limit/create', [StaffUserLimitController::class, 'store'])->name ('staff.limit.store');
		Route::get ('staff/user/limit/{limit}/edit', [StaffUserLimitController::class, 'edit'])->name ('staff.limit.edit');
		Route::post ('staff/user/limit/{limit}/edit', [StaffUserLimitController::class, 'update'])->name ('staff.limit.update');
		Route::get ('staff/user/limit/{limit}/remove', [StaffUserLimitController::class, 'remove'])->name ('staff.limit.remove');

		// Staff // User // Group //
		Route::get ('staff/user/group', [StaffGroupController::class, 'index'])->name ('staff.group.index');
		Route::get ('staff/user/group/create', [StaffGroupController::class, 'create'])->name ('staff.group.create');
		Route::post ('staff/user/group/create', [StaffGroupController::class, 'store'])->name ('staff.group.store');
		Route::get ('staff/user/group/{group}/remove', [StaffGroupController::class, 'remove'])->name ('staff.group.remove');


		// Staff // Website // vHost //
		Route::get ('staff/website/vhost', [StaffVHostController::class, 'index'])->name ('staff.vhost.index');
		Route::get ('staff/website/vhost/search', [StaffVHostController::class, 'search'])->name ('staff.vhost.search');
		Route::get ('staff/website/vhost/create', [StaffVHostController::class, 'create'])->name ('staff.vhost.create');
		Route::post ('staff/website/vhost/create', [StaffVHostController::class, 'store'])->name ('staff.vhost.store');
		Route::get ('staff/website/vhost/{vhost}/edit', [StaffVHostController::class, 'edit'])->name ('staff.vhost.edit');
		Route::post ('staff/website/vhost/{vhost}/edit', [StaffVHostController::class, 'update'])->name ('staff.vhost.update');
		Route::get ('staff/website/vhost/{vhost}/remove', [StaffVHostController::class, 'remove'])->name ('staff.vhost.remove');

		// Staff // FTP //
		Route::get ('staff/ftp', [StaffFtpController::class, 'index'])->name ('staff.ftp.index');
		Route::get ('staff/ftp/search', [StaffFtpController::class, 'search'])->name ('staff.ftp.search');
		Route::get ('staff/ftp/create', [StaffFtpController::class, 'create'])->name ('staff.ftp.create');
		Route::post ('staff/ftp/create', [StaffFtpController::class, 'store'])->name ('staff.ftp.store');
		Route::get ('staff/ftp/{ftp}/edit', [StaffFtpController::class, 'edit'])->name ('staff.ftp.edit');
		Route::post ('staff/ftp/{ftp}/edit', [StaffFtpController::class, 'update'])->name ('staff.ftp.update');
		Route::get ('staff/ftp/{ftp}/remove', [StaffFtpController::class, 'remove'])->name ('staff.ftp.remove');

		// Staff // Mail // Domain //
		Route::get ('staff/mail/domain', [StaffMailDomainController::class, 'index'])->name ('staff.mail.domain.index');
		Route::get ('staff/mail/domain/create', [StaffMailDomainController::class, 'create'])->name ('staff.mail.domain.create');
		Route::post ('staff/mail/domain/create', [StaffMailDomainController::class, 'store'])->name ('staff.mail.domain.store');
		Route::get ('staff/mail/domain/{mDomain}/edit', [StaffMailDomainController::class, 'edit'])->name ('staff.mail.domain.edit');
		Route::post ('staff/mail/domain/{mDomain}/edit', [StaffMailDomainController::class, 'update'])->name ('staff.mail.domain.update');
		Route::get ('staff/mail/domain/{mDomain}/remove', [StaffMailDomainController::class, 'remove'])->name ('staff.mail.domain.remove');

		// Staff // Mail //
		Route::get ('staff/mail/search', [StaffMailController::class, 'search'])->name ('staff.mail.search');

		// Staff // Mail // User //
		Route::get ('staff/mail/user', [StaffMailUserController::class, 'index'])->name ('staff.mail.user.index');
		Route::get ('staff/mail/user/create', [StaffMailUserController::class, 'create'])->name ('staff.mail.user.create');
		Route::post ('staff/mail/user/create', [StaffMailUserController::class, 'store'])->name ('staff.mail.user.store');
		Route::get ('staff/mail/user/{mUser}/edit', [StaffMailUserController::class, 'edit'])->name ('staff.mail.user.edit');
		Route::post ('staff/mail/user/{mUser}/edit', [StaffMailUserController::class, 'update'])->name ('staff.mail.user.update');
		Route::get ('staff/mail/user/{mUser}/remove', [StaffMailUserController::class, 'remove'])->name ('staff.mail.user.remove');

		// Staff // Mail // Forward //
		Route::get ('staff/mail/forward', [StaffMailForwardController::class, 'index'])->name ('staff.mail.forward.index');
		Route::get ('staff/mail/forward/create', [StaffMailForwardController::class, 'create'])->name ('staff.mail.forward.create');
		Route::post ('staff/mail/forward/create', [StaffMailForwardController::class, 'store'])->name ('staff.mail.forward.store');
		Route::get ('staff/mail/forward/{mFwd}/edit', [StaffMailForwardController::class, 'edit'])->name ('staff.mail.forward.edit');
		Route::post ('staff/mail/forward/{mFwd}/edit', [StaffMailForwardController::class, 'update'])->name ('staff.mail.forward.update');
		Route::get ('staff/mail/forward/{mFwd}/remove', [StaffMailForwardController::class, 'remove'])->name ('staff.mail.forward.remove');

		// Staff // Maintenance //
		Route::get ('staff/maintenance/vhost/generate', [StaffMaintenanceController::class, 'generateVHosts'])->name ('staff.maintenance.vhost.generate');
		Route::get ('staff/maintenance/vhost/save/all', [StaffMaintenanceController::class, 'saveAllVHosts'])->name ('staff.maintenance.vhost.save-all');
		Route::get ('staff/maintenance/service/generate', [StaffMaintenanceController::class, 'generateServiceData'])->name ('staff.maintenance.service.generate');
		Route::get ('staff/maintenance/system/check', [StaffMaintenanceController::class, 'systemCheck'])->name ('staff.maintenance.system.check');

		// Staff // Page //
		Route::get ('staff/page', [StaffPageController::class, 'index'])->name ('staff.page.index');
		Route::get ('staff/page/create', [StaffPageController::class, 'create'])->name ('staff.page.create');
		Route::post ('staff/page/create', [StaffPageController::class, 'store'])->name ('staff.page.store');
		Route::get ('staff/page/{page}/edit', [StaffPageController::class, 'edit'])->name ('staff.page.edit');
		Route::post ('staff/page/{page}/edit', [StaffPageController::class, 'update'])->name ('staff.page.update');
		Route::get ('staff/page/{page}/remove', [StaffPageController::class, 'remove'])->name ('staff.page.remove');

		// Staff // System //
		Route::get ('staff/system/phpinfo', [StaffSystemController::class, 'phpinfo'])->name ('staff.system.phpinfo');

		// Staff // System // Log //
		Route::get ('staff/system/log', [StaffSystemLogController::class, 'index'])->name ('staff.system.log.index');
		Route::get ('staff/system/log/search', [StaffSystemLogController::class, 'search'])->name ('staff.system.log.search');
		Route::get ('staff/system/log/laravel', [StaffSystemLogController::class, 'laravel'])->name ('staff.system.log.laravel');
		Route::get ('staff/system/log/{log}/show', [StaffSystemLogController::class, 'show'])->name ('staff.system.log.show');

		// Staff // System // SystemTask //
		Route::get ('staff/system/systemtask', [StaffSystemSystemTaskController::class, 'index'])->name ('staff.system.systemtask.index');
		Route::get ('staff/system/systemtask/create', [StaffSystemSystemTaskController::class, 'create'])->name ('staff.system.systemtask.create');
		Route::post ('staff/system/systemtask/create', [StaffSystemSystemTaskController::class, 'store'])->name ('staff.system.systemtask.store');
		Route::get ('staff/system/systemtask/{systemTask}/show', [StaffSystemSystemTaskController::class, 'show'])->name ('staff.system.systemtask.show');
		Route::get ('staff/system/systemtask/{systemTask}/remove', [StaffSystemSystemTaskController::class, 'remove'])->name ('staff.system.systemtask.remove');
	}
);
