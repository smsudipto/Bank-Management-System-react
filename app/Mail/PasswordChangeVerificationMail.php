<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangeVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $code,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your S4M Trust Bank password change');
    }

    public function content(): Content
    {
        return new Content(
            view: 'customer.security.password-verification-email',
            with: ['code' => $this->code],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
