<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserInfo;
use App\Models\UserLimit;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp (): void
	{
		parent::setUp ();

		$this->seed (DatabaseSeeder::class);
	}

	public function test_the_global_limits_row_exists (): void
	{
		// UserLimit::getLimit () falls back to this, and /user/start reads it
		$this->assertNotNull (UserLimit::whereNull ('uid')->first ());
	}

	public function test_the_seeded_passwords_verify_the_way_the_login_does (): void
	{
		foreach (['admin' => 'admin', 'penguin' => 'penguin'] as $username => $password)
		{
			$user = UserInfo::where ('username', $username)->firstOrFail ()->user;

			// UserController@login compares exactly like this
			$this->assertSame ($user->crypt, crypt ($password, $user->crypt),
				"the seeded password for $username does not verify");
			$this->assertNotSame ($user->crypt, crypt ('wrong', $user->crypt));
		}
	}

	public function test_only_the_staff_account_is_an_administrator (): void
	{
		$this->assertTrue (UserInfo::where ('username', 'admin')->firstOrFail ()->user->isAdmin ());
		$this->assertFalse (UserInfo::where ('username', 'penguin')->firstOrFail ()->user->isAdmin ());
	}

	public function test_the_seeded_users_never_expire (): void
	{
		foreach (User::all () as $user)
			$this->assertFalse ($user->hasExpired ());
	}
}
