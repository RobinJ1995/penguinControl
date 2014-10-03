<?php

class ApacheVhostVirtual
{
	private $properties = array ();
	
	const VHOSTDIRAVAILABLE = '/etc/apache2/sites-available/'; // Eindigen met een `/` //
	const VHOSTDIRENABLED = '/etc/apache2/sites-enabled/'; // Eindigen met een `/` //
	const SSLCERT = '/etc/apache2/ssl/wildcard.sinners.be.cert';
	const SSLKEY = '/etc/apache2/ssl/wildcard.sinners.be.key';
	const EXPIRED_DOCROOT = '/var/www/expired/';
	
	public static function get ($db)
	{
		$now = ceil (time () / 60 / 60 / 24);
		
		$q = $db->prepare
		(
			'SELECT apache_vhost_virtual.servername, apache_vhost_virtual.serveralias, apache_vhost_virtual.serveradmin, apache_vhost_virtual.ssl, user_info.username, group.name
			FROM apache_vhost_virtual
			INNER JOIN user ON apache_vhost_virtual.uid = user.uid
			INNER JOIN user_info ON user.user_info_id = user_info.id
			INNER JOIN `group` ON user.gid = `group`.gid
			WHERE user.expire <= :now AND user.expire != -1;'
		);
		$q->bindValue (':now', $now);
		$q->execute ();
		
		$r = $q->fetchAll ();
		
		$vHosts = array ();
		foreach ($r as $vHost)
			$vHosts[] = new ApacheVhostVirtual ($vHost['servername'], $vHost['serveralias'], $vHost['serveradmin'], $vHost['ssl'], $vHost['username'], $vHost['name']);
		
		return $vHosts;
	}
	
	public function __construct ($servername, $serveralias, $serveradmin, $ssl, $username, $group)
	{
		$this->servername = $servername;
		$this->serveralias = $serveralias;
		$this->serveradmin = $serveradmin;
		$this->ssl = $ssl;
		$this->username = $username;
		$this->group = $group;
	}
	
	public function __get ($property)
	{
		return $this->properties[$property];
	}
	
	public function nuke ()
	{
		$filename = 'VHOST_' . $this->username . '_' . $this->servername . '.conf';
		
		$template80 = 
'<VirtualHost *:80>
	ServerName {:servername:}
	ServerAdmin {:serveradmin:}
	ServerAlias {:serveralias:}
	AssignUserID {:username:} {:group:}

	DocumentRoot "{:docroot:}"
	<Directory "{:docroot:}">
		Options +Indexes +FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>

</VirtualHost>';
		$template443 = 
'<VirtualHost *:443>
	ServerName {:servername:}
	ServerAdmin {:serveradmin:}
	ServerAlias {:serveralias:}
	AssignUserID {:username:} {:group:}
	
	SSLEngine On
	SSLCertificateFile {:sslcert:}
	SSLCertificateKeyFile {:sslkey:}

	DocumentRoot "{:docroot:}"
	<Directory "{:docroot:}">
		Options +Indexes +FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>';
		
		$file = $template80 . ($this->ssl == 0 ? '' : PHP_EOL . $template443);
		
		$file = str_replace ('{:servername:}', $this->servername, $file);
		$file = str_replace ('{:serveralias:}', $this->serveralias, $file);
		$file = str_replace ('{:username:}', $this->username, $file);
		$file = str_replace ('{:group:}', $this->group, $file);
		$file = str_replace ('{:serveradmin:}', $this->serveradmin, $file);
		$file = str_replace ('{:docroot:}', self::EXPIRED_DOCROOT, $file);
		$file = str_replace ('{:sslcert:}', self::SSLCERT, $file);
		$file = str_replace ('{:sslkey:}', self::SSLKEY, $file);
		
		@unlink (self::VHOSTDIRAVAILABLE . $filename);
		@unlink (self::VHOSTDIRENABLED . $filename);
		
		$ok1 = file_put_contents (self::VHOSTDIRAVAILABLE . $filename, $file); // Bestand wordt overschreven wanneer reeds bestaat //
		$ok2 = symlink (self::VHOSTDIRAVAILABLE . $filename, self::VHOSTDIRENABLED . $filename);
		
		if ($ok1 === false) // Strict comparison (===) gebruiken! //
			return array
			(
				'exitcode' => 1,
				'output' => 'Kan niet schrijven naar bestand `' . self::VHOSTDIRAVAILABLE . $filename . '`'
			);
		if ($ok2 === false) // Strict comparison (===) gebruiken! //
			return array
			(
				'exitcode' => 1,
				'output' => 'Kan niet geen symlink schrijven naar `' . self::VHOSTDIRENABLED . $filename . '`'
			);
	}
}
