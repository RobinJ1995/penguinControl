<?php

class ApacheVhostVirtual extends LimitedUserOwnedModel
{
	protected $table = 'apache_vhost_virtual';
	public $timestamps = false;
	
	//const VHOSTDIRAVAILABLE = '/etc/apache2/sites-available/'; // Eindigen met een `/` //
	//const VHOSTDIRENABLED = '/etc/apache2/sites-enabled/'; // Eindigen met een `/` //
	const VHOSTDIRAVAILABLE = '/home/sincontrol/test/etc/apache2/sites-available/'; // Eindigen met een `/` //
	const VHOSTDIRENABLED = '/home/sincontrol/test/etc/apache2/sites-enabled/'; // Eindigen met een `/` //
	const SSLCERT = '/etc/apache2/ssl/wildcard.sinners.be.cert';
	const SSLKEY = '/etc/apache2/ssl/wildcard.sinners.be.key';
	const EXPIRED_DOCROOT = '/var/www/expired/';

	public function save (array $options = array ())
	{
		// Input should be sanitised in VHostController //
		$user = User::where ('uid', $this->uid)->first ();
		$username = $user->getUserInfo ()->username;
		$homedir = $user->homedir;
		$group = Group::where ('gid', $user->gid)->first ()->name;
		
		$identification = 'VHOST_' . $username . '_' . $this->servername;
		$filename = $identification . '.conf';
		
		$now = ceil (time () / 60 / 60 / 24);
		$expired = false;
		if ($user->expire <= $now && $user->expire != -1)
			$expired = true;
		
		$template80 = 
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

</VirtualHost>';
		$template443 = 
'<VirtualHost *:443>
	ServerName {:servername:}
	ServerAdmin {:serveradmin:}
	ServerAlias {:serveralias:}
	AssignUserID {:username:} {:group:}
	
	CustomLog "/var/log/apache2/vhost/{:identification:}" combined
	ErrorLog "{:homedir:}/logs/error_log"
	php_admin_value open_basedir "{:docroot:}:{:homedir:}/repos/:/tmp:/usr/share/php/{:basedir:}"

	CustomLog "/var/log/apache2/vhosts/{:identification:}.log" combined
	SSLEngine On
	SSLCertificateFile {:sslcert:}
	SSLCertificateKeyFile {:sslkey:}

	DocumentRoot "{:docroot:}"
	<Directory "{:docroot:}">
		{:cgiHandler:}
		Options {:execCGI:} +Indexes +FollowSymLinks
		AllowOverride {:overrides:}
		Require all granted
	</Directory>
</VirtualHost>';
		$templateRedirect = 
'<VirtualHost *:80>
	ServerName {:servername:}
	ServerAdmin {:serveradmin:}
	ServerAlias {:serveralias:}
	AssignUserID {:username:} {:group:}
	
	Redirect permanent / https://{:servername:}/
</VirtualHost>';
		
		$template = '';
		switch ($this->ssl)
		{
			case 0:
				$template = $template80;
				break;
			case 1:
				$template = $template443;
				break;
			case 2:
				$template = $templateRedirect . PHP_EOL . $template443;
				break;
		}
		
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
		$user = User::where ('uid', $this->uid)->first ();
		$username = $user->getUserInfo ()->username;
		
		$identification = 'VHOST_' . $username . '_' . $this->servername;
		$filename = $identification . '.conf';
		
		$ok1 = unlink (self::VHOSTDIRAVAILABLE . $filename);
		$ok2 = unlink (self::VHOSTDIRENABLED . $filename);
		
		if ($ok1 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan bestand `' . self::VHOSTDIRAVAILABLE . $filename . '` niet verwijderen');
		if ($ok2 === false) // Strict comparison (===) gebruiken! //
			throw new Exception ('Kan bestand `' . self::VHOSTDIRENABLED . $filename . '` niet verwijderen');
		
		return parent::delete ();
	}
}
