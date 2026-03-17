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
        return (new MailMessage)
            ->subject('Important Announcement: ' . $this->announcement->subject)
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line('A new internal announcement has been posted that requires your attention.')
            ->line('---')
            ->line('**Subject:** ' . $this->announcement->subject)
            ->line('**Summary:**')
            ->line($this->announcement->description)
            ->line('---')
            ->action('View Full Announcement', url('/announcements'))
            ->line('Stay informed and have a great day!')
            ->salutation('Regards, ' . config('app.name') . ' Team');
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
