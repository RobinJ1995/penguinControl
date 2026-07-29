<?php

namespace App\Mail;

use App\Models\UserInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a user that an administrator has validated their registration.
 */
class AccountActivated extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct (public UserInfo $userInfo)
	{
	}

	public function envelope (): Envelope
	{
		return new Envelope
		(
			to: [new Address ($this->userInfo->email, $this->userInfo->getFullName ())],
			subject: 'Your account has been activated'
		);
	}

	public function content (): Content
	{
		return new Content (view: 'email.user.activated');
	}
}
