<?php

namespace App\Notifications;

use App\Models\Position;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PositionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Position $position,
        public int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Position Expiring Soon: '.$this->position->title)
            ->line("Your position listing will expire in {$this->daysRemaining} day(s).")
            ->line("**Position:** {$this->position->title}")
            ->line("**Company:** {$this->position->company->name}")
            ->line('**Expires:** '.$this->position->expires_at->format('F j, Y'))
            ->action('Renew Position', route('hr.positions.show', $this->position->id))
            ->line('Renew your position to keep it visible to candidates.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'position_id' => $this->position->id,
            'position_title' => $this->position->title,
            'company_name' => $this->position->company->name,
            'expires_at' => $this->position->expires_at->toIso8601String(),
            'days_remaining' => $this->daysRemaining,
            'type' => 'position_expiring',
        ];
    }
}
