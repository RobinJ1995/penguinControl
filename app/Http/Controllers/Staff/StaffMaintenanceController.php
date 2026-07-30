<?php
/*
 * Do yourself a big favour and don't call these methods unless you understand
 * exactly what they do. There is a reason no user-friendly button in the panel
 * exposes them. They all serve a purpose and can solve a lot of problems
 * easily, but they can cause a great many more, even more easily, when used
 * incorrectly.
 *
 * -- Robin Jacobs (robinj1995)
 */

namespace App\Http\Controllers\Staff;

use App\AppException;
use App\Http\Controllers\Controller;
use App\Models\Ftp;
use App\Models\Group;
use App\Models\Log;
use App\Models\MailDomain;
use App\Models\MailForward;
use App\Models\MailUser;
use App\Models\Page;
use App\Models\SystemTask;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use App\Models\Vhost;
use App\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class StaffMaintenanceController extends Controller
{
	public function generateVHosts ()
	{
		try
		{
			$vhosts = Vhost::all ();
			$alerts = array ();

			foreach ($vhosts as $vhost)
			{
				$vhost->save ();

				$alerts[] = new Alert ('vHost created: ' . $vhost->servername, Alert::TYPE_SUCCESS);
			}
			
			$user = Auth::user ();
			$userInfo = $user->userInfo;
			
			Log::log ('vHosts regenerated');

			return view ('user.start', compact ('alerts', 'user', 'userInfo'));
		}
		catch (\Exception $ex)
		{
			return Redirect::to ('/error')->with ('ex', new AppException ($ex));
		}
	}
	
	public function saveAllVHosts () // Er... why does this function exist? Unless I'm blind tonight it does exactly the same as generateVHosts ()? //
	{
		try
		{
			$vhosts = Vhost::all ();
			$alerts = array ();
			
			foreach ($vhosts as $vhost)
			{
				$vhost->save ();
				
				$alerts[] = new Alert ('vHost re-saved: ' . $vhost->servername, Alert::TYPE_SUCCESS);
			}
			
			$user = Auth::user ();
			$userInfo = $user->userInfo;
			
			Log::log ('vHosts re-saved');
			
			return view ('user.start', compact ('alerts', 'user', 'userInfo'));
		}
		catch (\Exception $ex)
		{
			return Redirect::to ('/error')->with ('ex', new AppException ($ex));
		}
	}
	
	public function generateServiceData ()
	{
		$alerts = array ();
		
		try
		{
			DB::beginTransaction ();
			
			$users = User::all ();
			
			foreach ($users as $user)
			{
				$userInfo = $user->userInfo;
				
				$vhost = Vhost::makeDefaultFor ($user, $userInfo);

				if ($vhost === NULL)
					$alerts[] = new Alert ('No default vHost was created for ' . $userInfo->username . ': penguin.default_vhost_domain is not set.', 'warning');
				else
				{
					$vhost->save ();

					$alerts[] = new Alert ('vHost added: ' . $vhost->servername, Alert::TYPE_SUCCESS);
				}

				$ftp = new Ftp (); // User's default FTP account //
				$ftp->username = $userInfo->username;
				$ftp->uid = $user->uid;
				$ftp->passwd = $user->crypt;
				$ftp->dir = $user->homedir;
				$ftp->locked = 1; // Only editable by staff //
				$ftp->save ();

				$alerts[] = new Alert ('FTP account added: ' . $ftp->username, Alert::TYPE_SUCCESS);
			}
			
			DB::commit ();
			
			Log::log ('Service data generated');
			
			return Redirect::to ('/staff/user/user')->with ('alerts', $alerts);
		}
		catch (\Exception $ex) // ->with ('ex', $ex) apparently doesn't work // Serialization of 'Closure' is not allowed //
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('Creating the user data failed. All database transactions have been rolled back.', Alert::TYPE_ALERT)));
		}
	}
	
	public function systemCheck ()
	{
		try
		{
			DB::beginTransaction ();
			
			$alerts = array ();
			
			$users = User::all ();
			foreach ($users as $user)
			{
				if
				(
					empty ($user->id)
					|| empty ($user->uid)
					|| empty ($user->user_info_id)
					|| empty ($user->crypt)
					|| empty ($user->gcos)
					|| empty ($user->gid)
					|| empty ($user->homedir)
					|| empty ($user->shell)
					|| empty ($user->lastchange)
					|| empty ($user->expire)
				)
				{
					$alerts[] = new Alert ('User has missing fields: ' . $user->link (), 'warning');
				}
				
				$userInfo = $user->userInfo;
				
				if (empty ($userInfo))
				{
					$alerts[] = new Alert ('User has no associated row in the <kbd>user_info</kbd> table: ' . $user->link (), Alert::TYPE_ALERT);
					
					break;
				}
				
					if (! is_dir ($user->homedir))
					$alerts[] = new Alert ('User exists but their home directory does not: ' . $user->link (), Alert::TYPE_ALERT);
				
				if (is_dir ($user->homedir)){
					if (fileowner ($user->homedir) != $user->uid)
						$alerts[] = new Alert ('User exists but their home directory has the wrong owner: ' . $user->link (), Alert::TYPE_ALERT);
				}
				
				
			}
			
			$userInfos = UserInfo::all ();
			foreach ($userInfos as $userInfo)
			{
				if
				(
					empty ($userInfo->id)
					|| empty ($userInfo->username)
					|| empty ($userInfo->fname)
					|| empty ($userInfo->lname)
					|| empty ($userInfo->email)
				)
				{
					$alerts[] = new Alert ('User information has missing fields: ' . $userInfo->link (), 'warning');
				}
				
				if ($userInfo->validated == 1 && ( !$userInfo->userExists ()))
					$alerts[] = new Alert ('User information says the user is validated, but there is no row in the <kbd>user</kbd> table for that user: ' . $userInfo->link (), Alert::TYPE_ALERT);
			}
			
			$groups = Group::all ();
			foreach ($groups as $group)
			{
				if
				(
					empty ($group->id)
					|| empty ($group->name)
					|| empty ($group->gid)
					|| $group->passwd == 'x'
				)
				{
					$alerts[] = new Alert ('User group has missing fields: ' . $group->link (), 'warning');
				}
			}
			
			$ftps = Ftp::all ();
			foreach ($ftps as $ftp)
			{
				if
				(
					empty ($ftp->id)
					|| empty ($ftp->uid)
					|| empty ($ftp->username)
					|| empty ($ftp->passwd)
					|| empty ($ftp->dir)
				)
				{
					$alerts[] = new Alert ('FTP account has missing fields: ' . $ftp->link (), 'warning');
				}
			}
			
			$mailDomains = MailDomain::all ();
			foreach ($mailDomains as $domain)
			{
				if
				(
					empty ($domain->id)
					|| empty ($domain->uid)
					|| empty ($domain->domain)
				)
				{
					$alerts[] = new Alert ('E-mail domain has missing fields: ' . $domain->link (), 'warning');
				}
			}
			
			$mailUsers = MailUser::all ();
			foreach ($mailUsers as $mUser)
			{
				if
				(
					empty ($mUser->id)
					|| empty ($mUser->uid)
					|| empty ($mUser->email)
				    	|| empty ($mUser->password)
				)
				{
					$alerts[] = new Alert ('E-mail user has missing fields: ' . $mUser->link (), 'warning');
				}
			}
			
			$mailFwds = MailForward::all ();
			foreach ($mailFwds as $mFwd)
			{
				if
				(
					empty ($mFwd->id)
					|| empty ($mFwd->uid)
					|| empty ($mFwd->source)
				    	|| empty ($mFwd->destination)
				)
				{
					$alerts[] = new Alert ('Forwarding address has missing fields: ' . $mFwd->link (), 'warning');
				}
			}
			
			$pages = Page::all ();
			foreach ($pages as $page)
			{
				if
				(
					empty ($page->id)
					|| empty ($page->name)
					|| empty ($page->title)
				    	|| empty ($page->content)
				)
				{
					$alerts[] = new Alert ('Page has missing fields: ' . $page->link (), 'warning');
				}
			}
			
			$systemTasks = SystemTask::all ();
			foreach ($systemTasks as $task)
			{
				if
				(
					empty ($task->id)
					|| empty ($task->type)
				)
				{
					$alerts[] = new Alert ('System task has missing fields: ' . $task->link (), 'warning');
				}
				
				if ($task->started == 1 && (time () + 5 > $task->start) && empty ($task->exitcode))
					$alerts[] = new Alert ('System task should have started but has no exit code: ' . $task->link (), 'warning');
			}
			
			DB::commit ();
			
			$alerts[] = new Alert ('System check completed successfully', Alert::TYPE_SUCCESS);
			
			Log::log ('System check executed', NULL, $alerts);

			return Redirect::to ('/user/start')->with ('alerts', $alerts);
		}
		catch (\Exception $ex)
		{
			DB::rollback ();
			
			return Redirect::to ('/error')->with ('ex', new AppException ($ex))->with ('alerts', array (new Alert ('System check failed. If that works properly by now, all database transactions should have been rolled back.', Alert::TYPE_ALERT)));
		}
	}
}