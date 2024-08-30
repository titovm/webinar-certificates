<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Participant;
use Illuminate\Support\Facades\Storage;

class SendCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Participant $participant;

    /**
     * Create a new message instance.
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваш сертификат "' . $this->participant->certificate->name . '"',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate',
            with: [
                'participant' => $this->participant,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->participant->certificate_url) {
            $filePath = str_replace('/storage/', '', $this->participant->certificate_url);
            $file = Storage::disk('public')->path($filePath);

            $attachments[] = Attachment::fromPath($file)
                ->as('Сертификат '.$this->participant->name.'.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}