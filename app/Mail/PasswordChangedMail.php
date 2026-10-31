<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your S4M Trust Bank password was changed');
    }

    public function content(): Content
    {
        return new Content(view: 'customer.security.password-changed-email');
    }

    public function attachments(): array
    {
        return [];
    }
}
