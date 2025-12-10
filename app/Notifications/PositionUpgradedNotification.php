<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Position;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PositionUpgradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Position $position,
        public Payment $payment,
        public string $previousTier
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
            ->subject('Position Upgraded: '.$this->position->title)
            ->line('A position has been upgraded to a higher tier.')
            ->line("**Position:** {$this->position->title}")
            ->line("**Company:** {$this->position->company->name}")
            ->line('**Upgraded From:** '.ucfirst($this->previousTier))
            ->line('**Upgraded To:** '.ucfirst($this->payment->tier->value))
            ->line('**Upgrade Amount:** $'.number_format($this->payment->amount, 2))
            ->action('View Position', route('admin.positions.index'))
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
            'previous_tier' => $this->previousTier,
            'new_tier' => $this->payment->tier->value,
            'amount' => $this->payment->amount,
            'type' => 'position_upgraded',
        ];
    }
}
