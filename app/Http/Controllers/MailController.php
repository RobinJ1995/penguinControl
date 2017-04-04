<?php

namespace App\Http\Controllers;

use App\Alert;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class MailController extends Controller
{
	public function show ()
	{
		$user = Auth::user ();
		
		if ($user->mail_enabled)
			return view ('mail.disable', compact ('user'));
		else
			return view ('mail.enable', compact ('user'));
	}

	public function update ()
	{
		$user = Auth::user ();
		
		if (! empty (Input::get ('enable')))
			$user->mail_enabled = true;
		else if (! empty (Input::get ('disable')))
			$user->mail_enabled = false;
		
		$user->save ();
		
		Log::log ('E-mail ' . ($user->mail_enabled ? 'en' : 'dis') . 'abled', $user->id);
		
		return Redirect::to ('/mail')->with ('alerts', array (new Alert ('E-mail has been ' . ($user->mail_enabled ? 'en' : 'dis') . 'abled for your account.', Alert::TYPE_SUCCESS)));
	}
}