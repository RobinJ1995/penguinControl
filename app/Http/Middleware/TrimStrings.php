<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimmer;

class TrimStrings extends BaseTrimmer
{
	/**
	 * The names of the attributes that should not be trimmed.
	 *
	 * These are the password field names this application's forms actually use.
	 *
	 * @var array
	 */
	protected $except = [
		'password',
		'password_confirm',
		'password_confirmation',
		'passwd',
		'passwd_confirm',
		'currentPass',
		'newPass',
		'newPassConfirm'
	];
}
