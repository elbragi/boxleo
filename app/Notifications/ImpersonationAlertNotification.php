<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImpersonationAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User   $impersonator, // the admin who is doing the impersonation
        public User   $target,       // the account being impersonated
        public string $ip,
        public string $at,
        public bool   $isAdminCopy = false, // true when sent to other admins as audit
    ) {}

    public function via(object $notifiable): array
    {
        // Suppress all notifications when the developer account impersonates —
        // these are routine development/support sessions, not security events.
        if ($this->impersonator->id === 816) {
            return [];
        }

        // Audit copies to admins/HR are in-app only — no email.
        // The account owner (target) always receives an email.
        if ($this->isAdminCopy) {
            return ['database'];
        }

        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminName  = trim($this->impersonator->firstname . ' ' . $this->impersonator->lastname);
        $targetName = trim($this->target->firstname . ' ' . $this->target->lastname);

        if ($this->isAdminCopy) {
            return (new MailMessage)
                ->from('support@boxleocourier.com', 'Boxleo Security')
                ->subject('⚠️ Security Alert: Account Impersonation – ' . $targetName)
                ->greeting('Hello ' . $notifiable->firstname . ',')
                ->line('This is an automated security audit notification.')
                ->line("**" . $adminName . "** has started an impersonation session on the account of **" . $targetName . "**.")
                ->line('**Time:** ' . $this->at)
                ->line('**IP Address:** ' . $this->ip)
                ->line('No action is required if this was an authorised operation. If this is unexpected, please investigate immediately.')
                ->action('Open HRM', url('/dashboard'))
                ->salutation('Boxleo Security System');
        }

        return (new MailMessage)
            ->from('support@boxleocourier.com', 'Boxleo Security')
            ->subject('⚠️ Security Alert: Your Account Was Accessed by an Admin')
            ->greeting('Hello ' . $notifiable->firstname . ',')
            ->line("Your Boxleo account was accessed by an administrator using impersonation.")
            ->line('**Accessed by:** ' . $adminName)
            ->line('**Time:** ' . $this->at)
            ->line('**IP Address:** ' . $this->ip)
            ->line('If you authorised this (e.g. you requested IT support), no action is needed.')
            ->line('If this access was **unexpected**, please contact your HR administrator immediately.')
            ->action('Open HRM', url('/dashboard'))
            ->salutation('Boxleo Security Team');
    }

    public function toArray(object $notifiable): array
    {
        $adminName  = trim($this->impersonator->firstname . ' ' . $this->impersonator->lastname);
        $targetName = trim($this->target->firstname . ' ' . $this->target->lastname);

        if ($this->isAdminCopy) {
            return [
                'type'    => 'impersonation_audit',
                'message' => "{$adminName} impersonated {$targetName} at {$this->at} from IP {$this->ip}",
                'impersonator_id' => $this->impersonator->id,
                'target_id'       => $this->target->id,
                'ip'              => $this->ip,
                'at'              => $this->at,
            ];
        }

        return [
            'type'    => 'impersonation_alert',
            'message' => "Your account was accessed by {$adminName} at {$this->at}.",
            'impersonator_id' => $this->impersonator->id,
            'ip'              => $this->ip,
            'at'              => $this->at,
        ];
    }
}
