<?php

abstract class UserOwnedModel extends Eloquent
{
	public function getUser ()
	{
		return User::where ('uid', $this->uid)->first ();
	}
}