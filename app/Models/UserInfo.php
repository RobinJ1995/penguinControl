<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends BaseModel
{

	protected $table = 'user_info';
	public $timestamps = false;
	protected $hidden = array ('validationcode', 'logintoken', 'etc');
	
	public function getUser ()
	{
		return User::where ('user_info_id', $this->id)->first ();
	}
	
	public function userExists ()
	{
		return (User::where ('user_info_id', $this->id)->count () > 0);
	}
	
	public function userLog ()
	{
		return $this->hasMany ('\App\Models\UserLog');
	}
	
	public function user ()
	{
		return $this->hasOne ('\App\Models\User');
	}

	public function getFullName ()
	{
		return $this->fname . ' ' . $this->lname;
	}
	
	public function generateValidationCode ()
	{
		$this->validationcode = bin2hex (openssl_random_pseudo_bytes (16));
		
		return $this->validationcode;
	}
	
	public function generateLoginToken ()
	{
		$this->logintoken = bin2hex (openssl_random_pseudo_bytes (16));
		
		return $this->logintoken;
	}
	
	public function prepareHomedir () // Hoort aangeroepen te worden als root vanuit een SystemTask //
	{
		$group = $this->user->primaryGroup;
		$homedir = $this->user->homedir;
		
		if ($group == NULL)
			throw new Exception ('Group unknown');
		
		$cmd1 = 'cp -R /etc/skel/ ' . escapeshellarg ($homedir) . ' 2>&1';
		$cmd2 = 'chown ' . escapeshellarg ($this->username) . ':' . escapeshellarg ($group->name) . ' ' . escapeshellarg ($homedir) . ' -R 2>&1';
		
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
	
	public function url ()
	{
		if ($this->user != NULL)
			return action ('StaffUserController@more', $this->user->id);
		
		return NULL;
	}
	
	public function link ()
	{
		$url = $this->url ();
		$caption = get_class () . '#' . $this->id . ' (' . $this->username . ')';
		
		if ($url == NULL)
			return $caption;
		
		return '<a href="' . $url . '">' . $caption . '</a>';
	}
}
