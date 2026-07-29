<?php

namespace App\Models;


use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\BaseModel;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword, Notifiable;
	
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
		$this->crypt = crypt ($password, '$6$rounds=' . random_int (8000, 12000) . '$' . bin2hex (random_bytes (8)) . '$');
	}
	
	public function userInfo ()
	{
		return $this->belongsTo (UserInfo::class);
	}
	
	public function mailDomain ()
	{
		return $this->hasMany (MailDomain::class);
	}
	
	public function mailForward ()
	{
		return $this->hasMany (MailForward::class);
	}
	
	public function mailUser ()
	{
		return $this->hasMany (MailUser::class);
	}
	
	public function primaryGroup ()
	{
		return $this->hasOne (Group::class, 'gid', 'gid');
	}
	
	public function isGroupMember (Group $group)
	{
		return (UserGroup::where ('uid', $this->uid)->where ('gid', $group->gid)->count () > 0);
	}
	
	public function isAdmin ()
	{
		return $this->getLowestGid () <= 1050;
	}
	
	public function getLowestGid () // A lower gid means higher permissions //
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
		return $this->hasMany (Vhost::class, 'uid', 'uid');
	}
	
	public function url ()
	{
		return route ('staff.user.more', $this->id);
	}
	
	public function link ()
	{
		return '<a href="' . $this->url () . '">' . class_basename (static::class) . '#' . $this->id . ($this->userInfo != NULL ? ' (' . $this->userInfo->username . ')' : '') . '</a>';
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
		return $this->crypt;
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
		
		return 'User: ' . $name;
	}
	
	/**
	 * Get the name of the unique identifier for the user.
	 *
	 * @return string
	 */
	public function getAuthIdentifierName ()
	{
		return $this->getKeyName ();
	}
}
