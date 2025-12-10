<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Application $application,
        public string $previousStatus
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
        $statusLabels = [
            'pending' => 'Pending',
            'reviewing' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ];

        $message = (new MailMessage)
            ->subject('Application Status Updated: '.$this->application->position->title)
            ->line('Your application status has been updated.')
            ->line("**Position:** {$this->application->position->title}")
            ->line("**Company:** {$this->application->position->company->name}")
            ->line('**New Status:** '.($statusLabels[$this->application->status] ?? ucfirst($this->application->status)));

        if ($this->application->status === 'accepted') {
            $message->line('Congratulations! Your application has been accepted.')
                ->action('View Application', route('developer.applications.show', $this->application->id));
        } elseif ($this->application->status === 'rejected') {
            $message->line('We regret to inform you that your application was not successful this time.');
        } else {
            $message->action('View Application', route('developer.applications.show', $this->application->id));
        }

        return $message->line('Thank you for using our application!');
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
            'company_name' => $this->application->position->company->name,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->application->status,
            'type' => 'application_status_changed',
        ];
    }
}
