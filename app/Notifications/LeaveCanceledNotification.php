<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveCanceledNotification extends Notification
{
    use Queueable;

    protected $leave;
    protected $cancelledBy;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($leave, $cancelledBy = 'System', $reason = '')
    {
        $this->leave = $leave;
        $this->cancelledBy = $cancelledBy;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $recipientName = $notifiable->firstname ?: 'User';
        $subject = 'Leave Application Cancelled';
        
        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $recipientName . ',')
            ->line('Your leave application has been cancelled.')
            ->line('**Leave Details:**')
            ->line('**Duration:** ' . $this->leave->from . ' to ' . $this->leave->to)
            ->line('**Leave Type:** ' . $this->leave->leave_type->name)
            ->line('**Cancelled By:** ' . $this->cancelledBy);

        if ($this->reason) {
            $mail->line('**Reason:** ' . $this->reason);
        }

        return $mail
            ->action('View Leave Status', url('/leave-requests'))
            ->line('If you have any questions, please contact the HR department.')
            ->line('Thank you!');
    }
}
