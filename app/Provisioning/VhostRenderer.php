<?php

namespace App\Provisioning;

use App\Models\Group;
use App\Models\User;
use App\Models\Vhost;
use Illuminate\Support\Facades\Config;

/**
 * Turns a vHost row into the Apache configuration that serves it.
 *
 * This used to live inside Vhost::save (), interleaved with file_put_contents () and
 * symlink () against /etc/apache2 -- which meant the only way to find out what the panel
 * generates was to let it write to the web server's configuration directory. Rendering is
 * separated from applying so that the output can be asserted directly, and so that
 * whatever writes it can be something other than a web request.
 *
 * Values are validated rather than escaped. Apache has no general quoting mechanism for
 * directive arguments, so there is nothing to escape *to*: a ServerName containing a
 * newline is a new directive, and a document root containing a quote ends the argument.
 * The old code interpolated all of them with str_replace () and no checks at all, leaving
 * VHostController's validation as the only thing between a form field and Apache's
 * configuration.
 */
class VhostRenderer
{
	/**
	 * Hostnames, as ServerName and ServerAlias accept them. Wildcards are allowed
	 * because Apache allows them.
	 */
	private const HOSTNAME = '/^[A-Za-z0-9*]([A-Za-z0-9*_-]*[A-Za-z0-9*])?(\.[A-Za-z0-9*]([A-Za-z0-9*_-]*[A-Za-z0-9*])?)*$/';

	/**
	 * Absolute paths, with the characters that would break out of a quoted argument or
	 * start a new directive excluded outright.
	 */
	private const PATH = '/^\/[^"\r\n\\\\]*$/';

	public function render (Vhost $vhost): string
	{
		$user = User::where ('uid', $vhost->uid)->first ();

		if ($user === NULL)
			throw new ProvisioningException ('vHost ' . $vhost->servername . ' has no user with uid ' . $vhost->uid);

		$group = Group::where ('gid', $user->gid)->first ();

		if ($group === NULL)
			throw new ProvisioningException ('User ' . $user->uid . ' has no group with gid ' . $user->gid);

		$username = $this->requireName ($user->userInfo->username, 'username');
		$groupName = $this->requireName ($group->name, 'group name');
		$homedir = $this->requirePath ($user->homedir, 'home directory');

		$servername = $this->requireHostname ($vhost->servername, 'ServerName');
		$aliases = $this->aliases ($vhost);
		$serveradmin = $this->requireNoBreak ($vhost->serveradmin, 'ServerAdmin');

		$docroot = $this->requirePath (
			$vhost->hasExpired () ? self::expiredDocroot () : $vhost->docroot, 'document root');

		$identification = $vhost->identification ();
		$logdir = trailing_slash (self::path ('vhost_log'));

		$openBasedir = $docroot . ':' . $homedir . ':/tmp:/usr/share/php'
			. (empty ($vhost->basedir) ? '' : ':' . $this->requirePath ($vhost->basedir, 'additional base directory'));

		$lines = [];
		$lines[] = '<VirtualHost *:80>';
		$lines[] = "\tServerName " . $servername;
		$lines[] = "\tServerAdmin " . $serveradmin;

		if ($aliases !== '')
			$lines[] = "\tServerAlias " . $aliases;

		$lines[] = "\tAssignUserID " . $username . ' ' . $groupName;
		$lines[] = '';
		$lines[] = "\tCustomLog \"" . $logdir . $identification . '.log" combined';
		$lines[] = "\tErrorLog \"" . trailing_slash ($homedir) . 'logs/' . $identification . '_errors.log"';
		$lines[] = "\tphp_admin_value open_basedir \"" . $openBasedir . '"';
		$lines[] = '';
		$lines[] = "\tDocumentRoot \"" . $docroot . '"';
		$lines[] = "\t<Directory \"" . $docroot . '">';

		if ($vhost->cgi)
			$lines[] = "\t\tAddHandler cgi-script .cgi";

		$lines[] = "\t\tOptions " . ($vhost->cgi ? '+ExecCGI ' : '') . '+FollowSymLinks';
		$lines[] = "\t\tAllowOverride " . self::allowOverride ();
		$lines[] = "\t\tRequire all granted";
		$lines[] = "\t</Directory>";
		$lines[] = '';
		$lines[] = '</VirtualHost>';

		// Certbot needs the trailing newline; without it, it appends to the last line //
		return implode (PHP_EOL, $lines) . PHP_EOL;
	}

	/**
	 * ServerAlias takes a space-separated list, and the old code passed the column
	 * through whatever whitespace it happened to contain //
	 */
	private function aliases (Vhost $vhost): string
	{
		$aliases = preg_split ('/\s+/', trim ((string) $vhost->serveralias), -1, PREG_SPLIT_NO_EMPTY);

		foreach ($aliases as $alias)
			$this->requireHostname ($alias, 'ServerAlias');

		return implode (' ', $aliases);
	}

	private function requireHostname ($value, $what)
	{
		$value = trim ((string) $value);

		if ($value === '' || ! preg_match (self::HOSTNAME, $value))
			throw new ProvisioningException ('Refusing to generate a vHost: ' . $what . ' is not a hostname (' . $value . ')');

		return $value;
	}

	private function requirePath ($value, $what)
	{
		$value = (string) $value;

		if ($value === '' || ! preg_match (self::PATH, $value))
			throw new ProvisioningException ('Refusing to generate a vHost: ' . $what . ' is not a usable absolute path (' . $value . ')');

		return $value;
	}

	private function requireName ($value, $what)
	{
		$value = (string) $value;

		if ($value === '' || ! preg_match ('/^[a-z_][a-z0-9_-]*\$?$/i', $value))
			throw new ProvisioningException ('Refusing to generate a vHost: ' . $what . ' is not a usable name (' . $value . ')');

		return $value;
	}

	private function requireNoBreak ($value, $what)
	{
		$value = trim ((string) $value);

		if (preg_match ('/[\r\n]/', $value))
			throw new ProvisioningException ('Refusing to generate a vHost: ' . $what . ' spans more than one line');

		return $value;
	}

	public static function path ($key)
	{
		return (string) Config::get ('penguin.paths.' . $key);
	}

	public static function expiredDocroot ()
	{
		$configured = trim ((string) Config::get ('penguin.paths.expired_docroot'));

		return $configured === '' ? base_path ('static/expired') : $configured;
	}

	/**
	 * `All` lets a user's .htaccess turn on anything Apache will let an .htaccess turn
	 * on. It is the historical default and stays the default so that upgrading does not
	 * silently change what existing sites may do, but it is a setting now //
	 */
	public static function allowOverride ()
	{
		$value = trim ((string) Config::get ('penguin.apache.allow_override'));

		if ($value === '' || preg_match ('/[\r\n"]/', $value))
			return 'All';

		return $value;
	}
}
