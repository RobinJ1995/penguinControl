<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Page;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserInfo;
use App\Models\UserLimit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the minimum a penguinControl install needs in order to work at all,
 * plus one administrator and one ordinary user.
 *
 * The global user_limit row is not optional: UserLimit::getLimit () falls back
 * to it whenever a user has no row of their own, and the user's start page
 * reads several limits, so an install without it cannot render /user/start.
 */
class DatabaseSeeder extends Seeder
{
	/**
	 * Anything at or below this gid counts as an administrator.
	 *
	 * @see \App\Models\User::isAdmin()
	 */
	const ADMIN_GID = 1000;

	const USER_GID = 2000;

	/**
	 * The staff views compare a user's gid against the gid of the group named
	 * "user" to decide whether to render someone as staff, so that group has to
	 * exist under exactly that name.
	 *
	 * These names double as Unix group names, so neither may collide with a
	 * distribution's own groups -- which rules out "staff".
	 */
	const ADMIN_GROUP = 'panel';

	const USER_GROUP = 'user';

	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run ()
	{
		$this->seedLimits ();
		$this->seedPages ();

		$adminGroup = $this->seedGroup (self::ADMIN_GROUP, self::ADMIN_GID);
		$userGroup = $this->seedGroup (self::USER_GROUP, self::USER_GID);

		$this->seedUser ('admin', 'Ada', 'Lovelace', $adminGroup, 5000,
			$this->passwordFor ('SEED_ADMIN_PASSWORD', 'admin'));
		$this->seedUser ('penguin', 'Pingu', 'Pinguin', $userGroup, 5001,
			$this->passwordFor ('SEED_USER_PASSWORD', 'penguin'));
	}

	/**
	 * The seeded accounts used to ship with their username as their password, and
	 * README.md tells operators to run `db:seed --force` while installing -- so a stock
	 * install answered to admin/admin on the public internet.
	 *
	 * A password is now taken from the environment, and generated if none was given. The
	 * generated one is printed once, because it is not recoverable afterwards: `crypt` is
	 * one-way and nothing else stores it //
	 */
	private function passwordFor ($variable, $username)
	{
		$password = env ($variable);

		if (! empty ($password))
			return $password;

		$password = Str::password (20);

		$this->command?->warn (sprintf (
			'Generated a password for "%s": %s', $username, $password));
		$this->command?->warn (sprintf (
			'Set %s to choose your own. This is shown once.', $variable));

		return $password;
	}

	private function seedLimits ()
	{
		if (UserLimit::whereNull ('uid')->exists ())
			return;

		$limit = new UserLimit ();
		$limit->uid = NULL;
		$limit->ftp = 5;
		$limit->mysql_user_max = 5;
		$limit->mysql_db_max = 3;
		$limit->vhost = 5;
		$limit->mail_domain = 2;
		$limit->mail_user = 5;
		$limit->mail_forward = 5;
		$limit->diskusage = 100000;
		$limit->save ();
	}

	private function seedPages ()
	{
		if (Page::where ('name', 'home')->exists ())
			return;

		$page = new Page ();
		$page->name = 'home';
		$page->title = 'Home';
		$page->content = '<h1>Welcome</h1>';
		$page->published = 1;
		$page->weight = -127;
		$page->save ();
	}

	private function seedGroup ($name, $gid)
	{
		$group = Group::where ('gid', $gid)->first ();

		if (! empty ($group))
			return $group;

		$group = new Group ();
		$group->name = $name;
		$group->password = 'x';
		$group->gid = $gid;
		$group->save ();

		return $group;
	}

	private function seedUser ($username, $fname, $lname, Group $group, $uid, $password)
	{
		if (UserInfo::where ('username', $username)->exists ())
			return;

		$userInfo = new UserInfo ();
		$userInfo->username = $username;
		$userInfo->fname = $fname;
		$userInfo->lname = $lname;
		$userInfo->email = $username . '@localhost';
		$userInfo->schoolnr = '';
		$userInfo->validated = 1;
		$userInfo->save ();

		$user = new User ();
		$user->uid = $uid;
		$user->user_info_id = $userInfo->id;
		$user->gcos = $fname . ' ' . $lname;
		$user->gid = $group->gid;
		$user->homedir = '/home/' . $username;
		$user->shell = '/bin/bash';
		$user->smb_lm = '';
		$user->smb_nt = '';
		$user->diskusage = 0;
		$user->mail_enabled = 1;
		// -1 means the account never expires // See User::hasExpired () //
		$user->expire = -1;
		$user->setPassword ($password);
		$user->save ();

		$userGroup = new UserGroup ();
		$userGroup->uid = $user->uid;
		$userGroup->gid = $group->gid;
		$userGroup->save ();
	}
}
