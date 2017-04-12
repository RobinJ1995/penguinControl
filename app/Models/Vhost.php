<?php

namespace App\Models;

use App\LimitedUserOwnedModel;

class Vhost extends LimitedUserOwnedModel
{
	protected $table = 'vhost';
	public $timestamps = false;
	
	const VHOSTDIRAVAILABLE = '/etc/apache2/sites-available/'; // Eindigen met een `/` //
	const VHOSTDIRENABLED = '/etc/apache2/sites-enabled/'; // Eindigen met een `/` //
	//DEV// const VHOSTDIRAVAILABLE = '/home/sincontrol/test/etc/apache2/sites-available/'; // Eindigen met een `/` //
	//DEV// const VHOSTDIRENABLED = '/home/sincontrol/test/etc/apache2/sites-enabled/'; // Eindigen met een `/` //
	const SSLCERT = '/etc/apache2/ssl/wildcard.cert';
	const SSLKEY = '/etc/apache2/ssl/wildcard.key';
	const EXPIRED_DOCROOT = '/opt/penguincontrol/static/expired/';

	public function save (array $options = array ())
	{
		// Input should be sanitised in VHostController //
		$user = User::where ('uid', $this->uid)->first ();
		$username = $user->userInfo->username;
		$homedir = $user->homedir;
		$group = Group::where ('gid', $user->gid)->first ()->name;
		
		$identification = $this->identification ();
		$filename = $this->filename ();
		
		$now = ceil (time () / 60 / 60 / 24);
		$expired = false;
		if ($user->expire <= $now && $user->expire != -1)
			$expired = true;
		
		$template =
'<VirtualHost *:80>
	ServerName {:servername:}
	ServerAdmin {:serveradmin:}
	ServerAlias {:serveralias:}
	AssignUserID {:username:} {:group:}
	
	CustomLog "/var/log/apache2/vhost/{:identification:}.log" combined
	ErrorLog "{:homedir:}/logs/error_log"
	php_admin_value open_basedir "{:docroot:}:{:homedir:}/repos/:/tmp:/usr/share/php/{:basedir:}"

	DocumentRoot "{:docroot:}"
	<Directory "{:docroot:}">
		{:cgiHandler:}
		Options {:execCGI:} +Indexes +FollowSymLinks
		AllowOverride {:overrides:}
		Require all granted
	</Directory>

</VirtualHost>
'; // Needs an empty line at the end, otherwise Certbot has issues //
		
		$file = str_replace ('{:servername:}', $this->servername, $template);
		$file = str_replace ('{:serveralias:}', $this->serveralias, $file);
		$file = str_replace ('{:username:}', $username, $file);
		$file = str_replace ('{:homedir:}', $homedir, $file);
		$file = str_replace ('{:group:}', $group, $file);
		$file = str_replace ('{:serveradmin:}', $this->serveradmin, $file);
		$file = str_replace ('{:docroot:}', $expired ? self::EXPIRED_DOCROOT : $this->docroot, $file);
		$file = str_replace ('{:identification:}', $identification, $file);
		$file = str_replace ('{:execCGI:}', ($this->cgi ? '+ExecCGI' : ''), $file);
		$file = str_replace ('{:cgiHandler:}', ($this->cgi ? 'AddHandler cgi-script .cgi' : ''), $file);
		$file = str_replace ('{:sslcert:}', self::SSLCERT, $file);
		$file = str_replace ('{:sslkey:}', self::SSLKEY, $file);
		$file = str_replace ('{:basedir:}', empty ($this->basedir) ? '' : ':' . $this->basedir, $file);
		$file = str_replace ('{:overrides:}', 'FileInfo Indexes Limit AuthConfig Options', $file);
		
		@unlink (self::VHOSTDIRAVAILABLE . $filename);
		@unlink (self::VHOSTDIRENABLED . $filename);
		
		$ok1 = file_put_contents (self::VHOSTDIRAVAILABLE . $filename, $file); // Bestand wordt overschreven wanneer reeds bestaat //
		$ok2 = symlink (self::VHOSTDIRAVAILABLE . $filename, self::VHOSTDIRENABLED . $filename);
		
		if ($ok1 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan niet schrijven naar bestand `' . self::VHOSTDIRAVAILABLE . $filename . '`');
		if ($ok2 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan niet geen symlink schrijven naar `' . self::VHOSTDIRENABLED . $filename . '`');
		
		return parent::save ($options);
	}
	
	public function delete ()
	{
		$filename = $this->filename ();
		
		$ok1 = unlink (self::VHOSTDIRAVAILABLE . $filename);
		$ok2 = unlink (self::VHOSTDIRENABLED . $filename);
		
		if ($ok1 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan bestand `' . self::VHOSTDIRAVAILABLE . $filename . '` niet verwijderen');
		if ($ok2 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan bestand `' . self::VHOSTDIRENABLED . $filename . '` niet verwijderen');
		
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
		
		$cmd1 = 'mkdir -p ' . escapeshellarg ($this->docroot) . ' 2>&1';
		$cmd2 = 'chown ' . escapeshellarg ($username) . ':' . escapeshellarg ($groupName) . ' ' . escapeshellarg ($this->docroot) . ' -R 2>&1';
		
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
		return $this->hasOne ('\App\Models\User', 'uid', 'uid');
	}
	
	public function url ()
	{
		return action ('StaffVHostController@edit', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . '</a>';
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
		return self::VHOSTDIRENABLED . $this->filename ();
	}
	
	public function __toString ()
	{
		return 'vHost: ' . $this->servername . ' (' . $this->filename () . ')';
	}
}
