<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudyReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $employeeName,
        public array  $courses,   // [['title' => ..., 'progress' => ..., 'category' => ...], ...]
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📚 Your Weekly Study Reminder — Boxleo Staff Development',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.study-reminder',
        );
    }
}
