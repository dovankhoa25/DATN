<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đặt lại mật khẩu',
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password',
            with: ['url' => $this->url]
        );
    }


    public function attachments(): array
    {
        return [];
    }
}
