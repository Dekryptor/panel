<?php

namespace App\Mail;

use App\Models\DocumentSend;
use App\Support\Docs\DocsMarkdown;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentSend $send) {}

    public function envelope(): Envelope
    {
        $support = new Address((string) config('mail.support.address'), (string) config('mail.support.name'));

        return new Envelope(
            from: $support,
            bcc: array_map(fn (string $email) => new Address($email), $this->send->bcc ?? []),
            replyTo: [$support],
            subject: $this->send->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.documents',
            text: 'mail.documents-text',
            with: [
                'subject' => $this->send->subject,
                'html' => DocsMarkdown::render($this->send->message),
                'text' => $this->send->message,
            ],
        );
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return array_map(
            fn (array $document) => Attachment::fromStorageDisk('local', $document['path'])->as($document['filename'])->withMime('application/pdf'),
            $this->send->documents,
        );
    }
}
