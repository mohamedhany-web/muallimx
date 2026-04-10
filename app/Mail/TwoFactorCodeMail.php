<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public bool $forSystemSettings = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forSystemSettings
            ? 'رمز تأكيد تفعيل المصادقة الثنائية — ' . config('app.name')
            : 'رمز الدخول — ' . config('app.name');

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-code',
        );
    }
}
