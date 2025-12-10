<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct() {}

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
        $message = (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->line('Welcome to '.config('app.name').'! We\'re excited to have you on board.')
            ->line('Get started by:');

        if ($notifiable->isDeveloper()) {
            $message->line('- Complete your developer profile')
                ->line('- Browse available positions')
                ->line('- Apply to positions that match your skills')
                ->action('Complete Profile', route('developer.profile.edit'));
        } elseif ($notifiable->isHR()) {
            $message->line('- Set up your company profile')
                ->line('- Post your first job position')
                ->line('- Start receiving applications')
                ->action('Set Up Company', route('hr.company.setup'));
        } else {
            $message->line('- Explore the platform')
                ->action('Go to Dashboard', route('dashboard'));
        }

        return $message->line('Thank you for joining us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'user_role' => $notifiable->role,
        ];
    }
}
