<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public string $senderEmail;
    public string $phone;
    public string $contactSubject;
    public string $body;

    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $subject,
        string $body,
    ) {
        $this->senderName = $name;
        $this->senderEmail = $email;
        $this->phone = $phone;
        $this->contactSubject = $subject;
        $this->body = $body;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Contact Form: {$this->contactSubject}",
            replyTo: [$this->senderEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-form',
        );
    }
}
