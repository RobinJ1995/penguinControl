<?php

class MailController extends BaseController
{
	public function show ()
	{
		$user = Auth::user ();
		
		if ($user->mailEnabled)
			return View::make ('mail.disable', compact ('user'));
		else
			return View::make ('mail.enable', compact ('user'));
	}

	public function update ()
	{
		$user = Auth::user ();
		
		if (! empty (Input::get ('enable')))
			$user->mailEnabled = true;
		else if (! empty (Input::get ('disable')))
			$user->mailEnabled = false;
		
		$user->save ();
		
		SinLog::log ('E-mail ' . ($user->mailEnabled ? 'in' : 'uit') . 'geschakeld');
		
		return Redirect::to ('/mail')->with ('alerts', array (new Alert ('E-mail ' . ($user->mailEnabled ? 'in' : 'uit') . 'geschakeld', 'success')));
	}
}