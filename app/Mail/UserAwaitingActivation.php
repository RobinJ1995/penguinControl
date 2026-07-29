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
 * Notifies the administrators that a registration is waiting to be validated.
 */
class UserAwaitingActivation extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct (public UserInfo $userInfo)
	{
	}

	public function envelope (): Envelope
	{
		return new Envelope
		(
			to: [new Address (config ('penguin.admin_email', 'root@localhost'))],
			subject: 'User awaiting activation'
		);
	}

	public function content (): Content
	{
		return new Content (view: 'email.staff.user.awaiting_activation');
	}
}
