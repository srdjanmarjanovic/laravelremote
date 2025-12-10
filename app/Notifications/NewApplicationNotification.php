<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Application $application
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
            ->subject('New Application: '.$this->application->position->title)
            ->line('You have received a new application for your position.')
            ->line("**Position:** {$this->application->position->title}")
            ->line("**Applicant:** {$this->application->user->name}")
            ->line("**Email:** {$this->application->user->email}")
            ->action('View Application', route('hr.applications.show', $this->application->id))
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
            'application_id' => $this->application->id,
            'position_id' => $this->application->position_id,
            'position_title' => $this->application->position->title,
            'applicant_name' => $this->application->user->name,
            'applicant_email' => $this->application->user->email,
            'type' => 'new_application',
        ];
    }
}
