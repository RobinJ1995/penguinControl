<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Manages the DBMS accounts that users connect to their own databases with.
 *
 * These accounts are not application rows: they live in the DBMS itself, which
 * is why everything here goes through raw statements. The connection this runs
 * on therefore needs CREATE USER and GRANT OPTION.
 *
 * Two notes on DBMS compatibility, both verified against MariaDB 12.3:
 *
 *  - GRANT no longer creates a missing account. NO_AUTO_CREATE_USER has been
 *    part of the default sql_mode since MariaDB 10.2.4, and a GRANT naming an
 *    unknown account fails with "Can't find any matching row in the user
 *    table", so each GRANT is preceded by CREATE USER IF NOT EXISTS.
 *  - PASSWORD() and `SET PASSWORD FOR ... = '<hash>'` are still supported, so
 *    the hash stored in user_info.etc remains usable. (MySQL 8 removed both;
 *    this application targets MariaDB.)
 */
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

		if (! App::isLocal ())
			self::grant ($dbUsername, $username, $hash);
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
			throw new \Exception ("DBMS didn't return a valid password hash");

		return $hash;
	}

	public static function forUser (User $user)
	{
		$username = $user->userInfo->username;
		$password = self::generatePassword ();
		$dbUsername = 'pc_u' . $user->uid;

		self::grant ($dbUsername, $username, self::getHash ($password));

		return array ($dbUsername, $password);
	}

	/**
	 * Creates the account if it is missing, gives it the databases belonging to
	 * $username, and sets its password to an already-hashed value.
	 *
	 * @param string $dbUsername The DBMS account name
	 * @param string $username   The panel username, which its databases are named after
	 * @param string $hash       A hash as produced by the DBMS's PASSWORD()
	 *
	 * @return void
	 */
	private static function grant ($dbUsername, $username, $hash)
	{
		$pdo = DB::connection ()->getPdo ();

		$account = self::quoteAccount ($pdo, $dbUsername);

		$pdo->exec ("CREATE USER IF NOT EXISTS $account;");

		// The user's own database, named after them //
		$pdo->exec
		(
			'GRANT ALL PRIVILEGES '
			. 'ON ' . self::quoteIdentifier ($username) . '.* '
			. "TO $account;"
		);

		// And anything they prefix with their own name. The backslash escapes the
		// underscore so that it is matched literally rather than as a wildcard //
		$pdo->exec
		(
			'GRANT ALL PRIVILEGES '
			. 'ON ' . self::quoteIdentifier ($username . '\_%') . '.* '
			. "TO $account;"
		);

		$pdo->exec ("SET PASSWORD FOR $account = " . $pdo->quote ($hash) . ';');
	}

	/**
	 * Renders an account as 'name'@'%'.
	 *
	 * @return string
	 */
	private static function quoteAccount (PDO $pdo, $dbUsername)
	{
		return $pdo->quote ($dbUsername) . "@'%'";
	}

	/**
	 * Back-quotes a database name. Identifiers cannot be parameterised, so they
	 * are quoted by hand; a literal back-quote is escaped by doubling it.
	 *
	 * @return string
	 */
	private static function quoteIdentifier ($identifier)
	{
		return '`' . str_replace ('`', '``', $identifier) . '`';
	}

	private static function generatePassword ()
	{
		return self::generateRandom (16);
	}

	private static function generateRandom ($length)
	{
		return preg_replace ('/[^A-Za-z0-9 ]/', '', bin2hex (random_bytes ($length)));
	}
}
