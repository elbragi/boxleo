<?php

namespace App\Mail;

use App\Models\JobApplicant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class NewJobApplication extends Mailable
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
            subject: 'New Job Application: ' . $this->applicant->job->title,
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
            view: 'emails.recruitment.new_application',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];
        if ($this->applicant->cv) {
            $attachments[] = Attachment::fromStorageDisk('public', $this->applicant->cv)
                ->as('CV_' . $this->applicant->name . '.' . pathinfo($this->applicant->cv, PATHINFO_EXTENSION));
        }
        return $attachments;
    }
}
