<?php

namespace App\Notifications;

use App\Models\MunicipalityEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MunicipalityEventAlert extends Notification
{
    use Queueable;

    public function __construct(
        private readonly MunicipalityEvent $event
    ) {}

    /**
     * Deliver via both email and in-app database notification.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email format — follows the same pattern as ServiceRequestStatusUpdated.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📢 Event Alert: ' . $this->event->title)
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('The ' . $this->event->office->name . ' has announced a new event:')
            ->line('**' . $this->event->title . '**')
            ->line('📅 **Date & Time:** ' . $this->event->event_date->format('l, F j, Y \a\t g:i A'))
            ->line('📍 **Location:** ' . $this->event->place)
            ->when($this->event->occasion, fn ($mail) =>
                $mail->line('🎯 **Occasion:** ' . $this->event->occasion)
            )
            ->line('**Details:**')
            ->line($this->event->description)
            ->line('This alert was sent because you have interacted with ' . $this->event->office->name . '.')
            ->salutation('E-Services Portal');
    }

    /**
     * In-app database notification — stored in notifications table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'municipality_event',
            'event_id'     => $this->event->id,
            'title'        => $this->event->title,
            'office_name'  => $this->event->office->name,
            'event_date'   => $this->event->event_date->toISOString(),
            'place'        => $this->event->place,
            'occasion'     => $this->event->occasion,
            'preview'      => $this->event->office->name . ': ' . $this->event->title
                              . ' on ' . $this->event->event_date->format('M j, Y'),
        ];
    }
}