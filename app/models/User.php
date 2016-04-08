<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user';
	public $timestamps = false;

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array ('crypt', 'smb_lm', 'smb_nt', 'remember_token');
	
	public function setPassword ($password)
	{
		$this->crypt = crypt ($password, '$6$rounds=' . mt_rand (8000, 12000) . '$' . bin2hex (openssl_random_pseudo_bytes (64)) . '$');
	}
	
	public function getUserInfo () // Should be replaced by $user->userInfo everywhere //
	{
		return UserInfo::find ($this->user_info_id);
	}
	
	public function userInfo ()
	{
		return $this->belongsTo ('UserInfo');
	}
	
	public function mailDomainVirtual ()
	{
		return $this->hasMany ('MailDomainVirtual');
	}
	
	public function mailForwardingVirtual ()
	{
		return $this->hasMany ('MailForwardingVirtual');
	}
	
	public function mailUserVirtual ()
	{
		return $this->hasMany ('MailUserVirtual');
	}
	
	public function getGroup ()
	{
		return Group::where ('gid', $this->gid)->first ();
	}
	
	public function getGroups ()
	{
		return UserGroup::where ('uid', $this->uid)->get ();
	}
	
	public function primaryGroup ()
	{
		return $this->hasOne ('Group', 'gid', 'gid');
	}
	
	public function isGroupMember ($group)
	{
		return (UserGroup::where ('uid', $this->uid)->where ('gid', $group->gid)->count () > 0);
	}
	
	public function getLowestGid () // Lagere gid betekent hogere permissies //
	{
		$userGroups = UserGroup::where ('uid', $this->uid);
		$lowestGid = $this->gid;
		
		if ($userGroups->count () > 0 && $userGroups->min ('gid') < $lowestGid)
			$lowestGid = $userGroups->min ('gid');
		
		return $lowestGid;
	}
	
	public function hasExpired ()
	{
		$now = ceil (time () / 60 / 60 / 24);
		
		return ($this->expire <= $now && $this->expire != -1);
	}
	
	public function vhost ()
	{
		return $this->hasMany ('ApacheVhostVirtual', 'uid', 'uid');
	}
	
	public function url ()
	{
		return action ('StaffUserController@more', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . get_class () . '#' . $this->id . ($this->userInfo != NULL ? ' (' . $this->userInfo->username . ')' : '') . '</a>';
	}
	
	public function calculateDiskUsage ($save = false)
	{
		$exitStatus = NULL;
		$cmd = 'du -shbx ' . escapeshellarg ($this->homedir);
		
		$output = array ();
		exec ($cmd, $output, $exitStatus);

		if ($exitStatus == 0)
		{
			$usage = explode ("\t", implode (PHP_EOL, $output))[0];
			
			if ($save)
			{
				$this->diskusage = $usage;
				
				$this->save ();
			}
			
			return $usage;
		}
	}
	
	public static function calculateAndSaveDiskUsage ()
	{
		$now = time () / 60 / 60 / 24;
		
		foreach (User::where ('expire', '>', $now)->orWhere ('expire', -1)->get () as $user)
			$user->calculateDiskUsage (true);
	}

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier ()
	{
		return $this->getKey ();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword ()
	{
		return $this->password;
	}

	/**
	 * Get the token value for the "remember me" session.
	 *
	 * @return string
	 */
	public function getRememberToken ()
	{
		return $this->remember_token;
	}

	/**
	 * Set the token value for the "remember me" session.
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setRememberToken ($value)
	{
		$this->remember_token = $value;
	}

	/**
	 * Get the column name for the "remember me" token.
	 *
	 * @return string
	 */
	public function getRememberTokenName ()
	{
		return 'remember_token';
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail ()
	{
		return $this->email;
	}
	
	public function __toString ()
	{
		$name = '#' . $this->id;
		
		if ($this->userInfo != NULL)
			$name = $this->userInfo->username;
		
		return 'Gebruiker: ' . $name;
	}

}
