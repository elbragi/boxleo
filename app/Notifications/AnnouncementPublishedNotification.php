<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementPublishedNotification extends Notification
{
    use Queueable;

    protected $announcement;
    protected $shouldSendMail;


    /**
     * Create a new notification instance.
     *
     * @param $announcement
     */

    /**
     * Create a new notification instance.
     */
    public function __construct($announcement, $shouldSendMail = true)
    {
        $this->announcement = $announcement;
        $this->shouldSendMail = $shouldSendMail;
    }

    /**
     * Get the notification's delivery channels.
     *@param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($this->shouldSendMail) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @param object $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        $authorName = \App\Models\User::find($this->announcement->author)?->firstname ?? 'Admin';
        $publishDate = \Carbon\Carbon::parse($this->announcement->publish_date)->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->from('support@boxleocourier.com', 'Boxleo Support')
            ->subject('Update: ' . $this->announcement->subject)
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line('A new internal announcement has been published by ' . $authorName . ' on ' . $publishDate . '.')
            ->line('**Subject:** ' . $this->announcement->subject)
            ->line('**Description:**')
            ->line($this->announcement->description)
            ->action('View Full Announcement', url('/announcements/'))
            ->line('Stay informed and have a great day!')
            ->salutation('Best Regards,')
            ->line('Boxleo IT Support');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'subject' => $this->announcement->subject,
            'message' => 'A new announcement has been published.',
        ];
    }
}
