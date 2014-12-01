<?php

abstract class UserOwnedModel extends Eloquent
{
	public function getUser ()
	{
		return User::where ('uid', $this->uid)->first ();
		// return User::where ('user_info_id', $this->user_info_id)->first ();
	}
}