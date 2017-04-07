<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DatabaseCredentials
{
	public static function forUserPrimary ($username, $password)
	{
		$hash = self::getHash ($password);
		self::forUserPrimary_hash ($username, $hash);
	}
	
	public static function forUserPrimary_hash ($username, $hash)
	{
		$dbUsername = $username;
		
		$pdo = DB::connection ()->getPdo ();
		
		if (! App::isLocal ())
		{
			$pdo->exec
			(
				"GRANT ALL PRIVILEGES "
				. "ON `$username`.* "
				. "TO '$dbUsername'@'%';"
			);

			$pdo->exec
			(
				"GRANT ALL PRIVILEGES "
				. "ON `" . $username . "\_%`.* "
				. "TO '$dbUsername'@'%';"
			);

			$pdo->exec
			(
				"SET PASSWORD "
				. "FOR '$dbUsername'@'%' = '$hash';"
			);
		}
	}
	
	public static function getHash ($password)
	{
		$pdo = DB::connection ()->getPdo ();
		$q = $pdo->prepare ('SELECT PASSWORD(:pass);');
		$q->bindValue (':pass', $password);
		$q->execute ();
		$result = $q->fetchAll ();
		
		$hash = $result[0][0];
		
		if (empty ($hash))
			throw new Exception ("DBMS didn't return a valid password hash");
		
		return $hash;
	}
	
	public static function forUser (User $user)
	{
		$username = $user->userInfo->username;
		$password = self::generatePassword ();
		$dbUsername = 'pc_u' . $user->uid;
		
		$pdo = DB::connection ()->getPdo ();

		$pdo->exec
		(
			"GRANT ALL PRIVILEGES "
			. "ON `$username`.* "
			. "TO '$dbUsername'@'%';"
		);

		$pdo->exec
		(
			"GRANT ALL PRIVILEGES "
			. "ON `" . $username . "\_%`.* "
			. "TO '$dbUsername'@'%';"
		);
		
		$pdo->exec
		(
			"SET PASSWORD "
			. "FOR '$dbUsername'@'%' = PASSWORD('$password');"
		);
		
		return array ($dbUsername, $password);
	}
	
	private static function generatePassword ()
	{
		return self::generateRandom (16);
	}
	
	private static function generateRandom ($length)
	{
		return preg_replace('/[^A-Za-z0-9 ]/', '', bin2hex (openssl_random_pseudo_bytes ($length)));
	}

}
