<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestCreated extends Notification
{
    use Queueable;

    public function __construct(private ServiceRequest $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('New Service Request Submitted!')
            ->line('A citizen has submitted a new service request.')
            ->line('Service: '.$this->serviceRequest->service->name)
            ->line('Office: '.$this->serviceRequest->service->office->name)
            ->line('Citizen: '.$this->serviceRequest->citizen->name)
            ->action('View Request', route('municipality.service-requests.show', $this->serviceRequest))
            ->line('Thank you for using the E-Services Platform.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'citizen_name' => $this->serviceRequest->citizen->name,
            'service_name' => $this->serviceRequest->service->name,
        ];
    }
}
