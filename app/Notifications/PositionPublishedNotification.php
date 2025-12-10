<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Position;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PositionPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Position $position,
        public Payment $payment
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
            ->subject('New Position Published: '.$this->position->title)
            ->line('A new position has been published on the platform.')
            ->line("**Position:** {$this->position->title}")
            ->line("**Company:** {$this->position->company->name}")
            ->line('**Tier:** '.ucfirst($this->payment->tier->value))
            ->line('**Amount Paid:** $'.number_format($this->payment->amount, 2))
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
            'tier' => $this->payment->tier->value,
            'amount' => $this->payment->amount,
            'type' => 'position_published',
        ];
    }
}
