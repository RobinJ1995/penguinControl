<?php

namespace App\Models;

use App\LimitedUserOwnedModel;
use App\Provisioning\VhostRenderer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Vhost extends LimitedUserOwnedModel
{
	protected $table = 'vhost';
	public $timestamps = false;
	
	/*
	 * These were constants. SSLCERT and SSLKEY pointed at a wildcard certificate from
	 * before Certbot and were referenced by nothing at all, so they are gone rather than
	 * relocated. The rest are penguin.paths.* now // See App\Provisioning\VhostRenderer //
	 */
	public static function dirAvailable ()
	{
		return trailing_slash (VhostRenderer::path ('vhost_available'));
	}

	public static function dirEnabled ()
	{
		return trailing_slash (VhostRenderer::path ('vhost_enabled'));
	}

	public static function logDir ()
	{
		return trailing_slash (VhostRenderer::path ('vhost_log'));
	}

	/**
	 * Whether the owner of this vHost has expired, in which case it serves the expired
	 * placeholder instead of the user's own document root //
	 */
	public function hasExpired ()
	{
		$user = User::where ('uid', $this->uid)->first ();

		return $user !== NULL && $user->hasExpired ();
	}

	/*
	 * The vHost a user gets when staff approve them, or NULL when the install has no
	 * default domain configured -- in which case staff add one by hand.
	 *
	 * The domain was hardcoded to the original deployment's, in both of the two places
	 * that build this, so a new install created vHosts for a domain it did not own //
	 */
	public static function makeDefaultFor (User $user, UserInfo $userInfo)
	{
		$domain = trim ((string) Config::get ('penguin.default_vhost_domain'));

		if ($domain === '')
			return NULL;

		$vhost = new self ();
		$vhost->uid = $user->uid;
		$vhost->docroot = $user->homedir . '/public_html';
		$vhost->servername = $userInfo->username . '.' . $domain;
		$vhost->serveralias = 'www.' . $userInfo->username . '.' . $domain;
		$vhost->serveradmin = $userInfo->username . '@' . $domain;
		$vhost->cgi = 1;
		$vhost->ssl = 0;
		$vhost->locked = 1; // Only editable by staff //

		return $vhost;
	}

	public function save (array $options = array ())
	{
		$filename = $this->filename ();

		// Rendering lives in VhostRenderer, which validates every value it interpolates.
		// This used to build the file inline with str_replace () and no checks //
		$file = (new VhostRenderer ())->render ($this);

		$available = self::dirAvailable () . $filename;
		$enabled = self::dirEnabled () . $filename;

		// Apache refuses to start if a CustomLog directory is missing //
		if (! is_dir (self::logDir ()))
			@mkdir (self::logDir (), 0755, true);

		@unlink ($available);
		@unlink ($enabled);

		$ok1 = file_put_contents ($available, $file); // Overwrites the file if it already exists //
		$ok2 = symlink ($available, $enabled);

		if ($ok1 === false) // Use strict comparison (===)! //
			throw new \Exception ('Can\'t write to file. `' . $available . '`');
		if ($ok2 === false) // Use strict comparison (===)! //
			throw new \Exception ('Can\'t write symlink to `' . $enabled . '`');

		return parent::save ($options);
	}

	public function delete ()
	{
		$filename = $this->filename ();
		
		$ok1 = unlink (self::dirAvailable () . $filename);
		$ok2 = unlink (self::dirEnabled () . $filename);
		
		if ($ok1 === false) // Use strict comparison (===)! //
			throw new \Exception ('Can\'t remove file `' . self::dirAvailable () . $filename . '`');
		if ($ok2 === false) // Use strict comparison (===)! //
			throw new \Exception ('Can\'t remove file `' . self::dirEnabled () . $filename . '`');
		
		return parent::delete ();
	}
	
	public static function nukeExpired ()
	{
		$now = time () / 60 / 60 / 24;
		
		$expiredUsers = User::where ('expire', '<=', $now)
			->where ('expire', '>', -1)
			->get ();
		
		foreach ($expiredUsers as $user)
		{
			foreach ($user->vhost as $vhost)
				$vhost->save ();
		}
	}
	
	public function createDocroot ()
	{
		$username = $this->user->userInfo->username;
		$groupName = $this->user->primaryGroup->name;
		$homedir = $this->user->homedir;
		$docrootTop = $this->docroot;
		
		if (strlen (trailing_slash ($this->docroot)) > strlen (trailing_slash ($homedir)) && Str::startsWith ($this->docroot, $homedir))
		{
			$slashPos = strpos ($this->docroot, '/', strlen (trailing_slash ($homedir)));
			$docrootTop = substr ($this->docroot, 0, $slashPos);
		}
		
		$cmd1 = 'mkdir -p ' . escapeshellarg ($this->docroot) . ' 2>&1';
		$cmd2 = 'chown ' . escapeshellarg ($username) . ':' . escapeshellarg ($groupName) . ' ' . escapeshellarg ($docrootTop) . ' -R 2>&1';
		
		$output = array ();
		
		exec ($cmd1, $output, $exitStatus1);
		exec ($cmd2, $output, $exitStatus2);
		
		return array
		(
			'exitcode' => max ($exitStatus1, $exitStatus2),
			'command' => array ($cmd1, $cmd2),
			'output' => implode (PHP_EOL, $output)
		);
	}
	
	public function user ()
	{
		return $this->hasOne (User::class, 'uid', 'uid');
	}
	
	public function url ()
	{
		return route ('staff.vhost.edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . '</a>';
	}
	
	public function identification ()
	{
		return 'VHOST_' . User::where ('uid', $this->uid)->firstOrFail ()->userInfo->username . '_' . $this->servername;
	}
	
	public function filename ()
	{
		return $this->identification () . '.conf';
	}
	
	public function path ()
	{
		return self::dirEnabled () . $this->filename ();
	}
	
	public function __toString ()
	{
		return 'vHost: ' . $this->servername . ' (' . $this->filename () . ')';
	}
	
	public function resolve ()
	{
		$hosts = [ $this->servername ];
		foreach (explode (' ', $this->serveralias) as $alias)
			$hosts[] = $alias;
		
		$resolved = [];
		foreach ($hosts as $host)
			$resolved[$host] = gethostbyname ($host);
		
		return $resolved;
	}
	
	public function isResolvingToThisServer ()
	{
		$serverIP = Config::get ('penguin.server_ip');
		
		foreach ($this->resolve () as $ip)
		{
			if ($ip != $serverIP)
				return false;
		}
		
		return true;
	}
}
