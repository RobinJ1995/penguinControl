<?php

namespace Plugin\TownCMSInstaller;

use App\Models\Vhost;
use Illuminate\Support\Facades\DB;

class TownCMSManager
{
	private $vhost;

	public function __construct (Vhost $vhost)
	{
		$this->vhost = $vhost;
	}

	public function install ()
	{
		$username = $this->vhost->user->userInfo->username;
		$domain   = $this->vhost->servername;
		$homedir = $this->vhost->docroot;

		$user = substr($domain, 0, strrpos($domain,"."));
		$dbUsername = $username . '_' . $user;

		echo $dbUsername . PHP_EOL;

		$dirPath = substr($homedir, 0, strrpos($homedir, "/", -2));

		echo $dirPath . PHP_EOL;

		define ('TOWN_CMS_GIT_REPO', 'gogs@git.webtown.ie:robinjacobs/town-cms.git');
		define ('SSH_KEY', '~/.ssh/penguincontrol_towncms_autoinstall');

		if (empty ($username) || empty ($domain))
			die ('Specify username and domain (Example: "php setup.php ctballjewellers ctballjewellers.town.ie")');
		if (strlen ($username) > 16 || strlen ($username) < 5)
			die ('Username needs to be between 5 and 16 characters long');

		echo 'Generating password and key...' . PHP_EOL;
		$password      = bin2hex (openssl_random_pseudo_bytes (10));
		$passwordCrypt = password_hash ($password, PASSWORD_DEFAULT);
		$key           = bin2hex (openssl_random_pseudo_bytes (32));
		$sedMap        = [
			'{:DOMAIN:}'   => $domain,
			'{:USERNAME:}' => $username,
			'{:PASSWORD:}' => $password,
			'{:TOWN_KEY:}' => $key
		];

		$pdo = DB::connection ()->getPdo ();

		echo 'Connecting to database...' . PHP_EOL;
		$pdo->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		echo 'Creating database user and granting privileges...' . PHP_EOL;
		$pdo->exec ("CREATE DATABASE `$dbUsername`;");
		$pdo->exec ("GRANT ALL PRIVILEGES ON `$dbUsername`.* TO '$username'@'%';");

		// Remove the public folder from the document root
		echo $homedir . PHP_EOL;

		$switchDir = 'cd ' . $homedir;

		exec ($switchDir);

		/*if ( ! chdir ("$dirPath"))
			die ("Switching to $dirPath failed");*/

		echo 'Cloning Git repository...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('GIT_SSH_COMMAND="ssh -i ' . escapeshellarg (SSH_KEY) . '" git clone ' . escapeshellarg (TOWN_CMS_GIT_REPO), $output, $exitCode). ' sudo -u ' . escapeshellarg ($username);
		if ($exitCode !== 0)
			die ('Cloning Git repository failed...' . PHP_EOL . implode (PHP_EOL, $output));

		echo 'Installing dependencies...' . PHP_EOL;

		$townCMS = 'cd town-cms';
		/*if ( ! chdir ("$homedir/town-cms"))
			die ("Switching to $homedir/town-cms failed");*/

		exec ($townCMS);

		unset ($output, $exitCode);
		exec ('composer install', $output, $exitCode);
		if ($exitCode !== 0)
			die ('`composer install` failed...' . PHP_EOL . implode (PHP_EOL, $output));

		echo 'Setting up the CMS...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('cp .env.example .env', $output, $exitCode);
		if ($exitCode !== 0)
			die ('Copying .env file failed...' . PHP_EOL . implode (PHP_EOL, $output));

		unset ($output, $exitCode);
		exec ('php artisan key:generate', $output, $exitCode);
		if ($exitCode !== 0)
			die ('`php artisan key:generate` failed...' . PHP_EOL . implode (PHP_EOL, $output));

		foreach ($sedMap as $placeholder => $value) {
			$sedStr = 's/' . $placeholder . '/' . $value . '/g';

			unset ($output, $exitCode);
			exec ('sed -i ' . escapeshellarg ($sedStr) . ' .env', $output, $exitCode);
			if ($exitCode !== 0)
				die ('Placeholder replacement with `sed` failed...' . PHP_EOL . implode (PHP_EOL, $output));
		}

		echo 'Generating vHost...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('sudo cp /etc/apache2/sites-available/template.conf ' . escapeshellarg ('/etc/apache2/sites-available/' . $user . '.conf'), $output, $exitCode);
		if ($exitCode !== 0)
			die ('Copying vHost template failed...' . PHP_EOL . implode (PHP_EOL, $output));

		foreach ($sedMap as $placeholder => $value) {
			$sedStr = 's/' . $placeholder . '/' . $value . '/g';

			unset ($output, $exitCode);
			exec ('sudo sed -i ' . escapeshellarg ($sedStr) . ' ' . escapeshellarg ('/etc/apache2/sites-available/' . $user . '.conf'), $output, $exitCode);
			if ($exitCode !== 0)
				die ('Placeholder replacement with `sed` failed...' . PHP_EOL . implode (PHP_EOL, $output));
		}

		echo 'Enabling vHost...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('sudo a2ensite ' . escapeshellarg ($user), $output, $exitCode);
		if ($exitCode !== 0)
			die ('Enabling vHost failed...' . PHP_EOL . implode (PHP_EOL, $output));

		echo 'Testing Apache configuration...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('sudo apache2ctl configtest', $output, $exitCode);
		if ($exitCode !== 0)
			die ('Configuration test failed...' . PHP_EOL . implode (PHP_EOL, $output));

		echo 'Reloading Apache configuration...' . PHP_EOL;
		unset ($output, $exitCode);
		exec ('sudo systemctl reload apache2', $output, $exitCode);
		if ($exitCode !== 0)
			die ('Reloading Apache failed...' . PHP_EOL . implode (PHP_EOL, $output));

		echo PHP_EOL . '---' . PHP_EOL;
		echo 'Setup completed!' . PHP_EOL;
		echo '---' . PHP_EOL;
		echo 'Website: http://' . $domain . '/' . PHP_EOL;
		echo 'Username: ' . $username . PHP_EOL;
		echo 'Password: ' . $password . PHP_EOL;
		echo 'Key: ' . $key . PHP_EOL;
		echo '---' . PHP_EOL;

		return array
		(
			'exitcode' => $exitCode,
			'output'   => implode (PHP_EOL, $output)
		);
	}
}