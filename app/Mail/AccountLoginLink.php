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
 * Carries the one-time login link issued by the "amnesia" flow.
 */
class AccountLoginLink extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct (public UserInfo $userInfo, public string $url)
	{
	}

	public function envelope (): Envelope
	{
		return new Envelope
		(
			to: [new Address ($this->userInfo->email, $this->userInfo->getFullName ())],
			subject: 'Account login information'
		);
	}

	public function content (): Content
	{
		return new Content (view: 'email.user.amnesia');
	}
}
