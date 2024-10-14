<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    protected $recovery_code;
    public $name;

    public function __construct($recovery_code,$name)
    {
        $this->recovery_code = $recovery_code;
        $this->name=$name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Safety and security',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email-password',
        );
    }

    public function build()
    {
        return $this->subject('Update Password')
            ->view('emails.email-password') // تأكد من أن العرض 'emails.email-verification' موجود ويحتوي على الرابط للتحقق
            ->with(['name' => $this->name, 'recovery_code' => $this->recovery_code]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
