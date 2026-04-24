<?php

namespace App\Mail;

use App\Models\JobApplicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(JobApplicant $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Application Received: ' . $this->applicant->job->title,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.recruitment.candidate_reply',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
