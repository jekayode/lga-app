<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoordinatorReferralCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $coordinator) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Lagos Grassroots Alliance coordinator referral code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.coordinator-referral-code',
            with: [
                'signupUrl' => route('become-agent', ['ref' => $this->coordinator->referral_code]),
                'setPasswordUrl' => route('password.request'),
                'loginUrl' => route('login'),
            ],
        );
    }
}
