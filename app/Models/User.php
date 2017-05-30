<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Permission;

class User extends Authenticatable
{
	use Notifiable;
	
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
	
	public function userInfo ()
	{
		return $this->belongsTo ('\App\Models\UserInfo');
	}
	
	public function mailDomain ()
	{
		return $this->hasMany ('\App\Models\MailDomain');
	}
	
	public function mailForward ()
	{
		return $this->hasMany ('\App\Models\MailForward');
	}
	
	public function mailUser ()
	{
		return $this->hasMany ('\App\Models\MailUser');
	}
	
	public function primaryGroup ()
	{
		return $this->hasOne ('\App\Models\Group', 'gid', 'gid');
	}
	
	public function isGroupMember (Group $group)
	{
		return (UserGroup::where ('uid', $this->uid)->where ('gid', $group->gid)->count () > 0);
	}
	
	public function isAdmin ()
	{
		return $this->getLowestGid () <= 1050;
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
		return $this->hasMany ('Vhost', 'uid', 'uid');
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
		$cmd = 'du -shbxB 1048576 ' . escapeshellarg ($this->homedir);
		
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
	
	public function getLimit ($property)
	{
		return UserLimit::getLimit ($this, $property);
	}
	
	public function label ()
	{
		return htmlstr (view ('part.user.label')->with ('user', $this));
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
