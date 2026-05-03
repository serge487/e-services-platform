<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private ServiceRequest $serviceRequest,
        private string $newStatus,
        private ?string $notes = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = match($this->newStatus) {
            'In Review' => 'Your request has been accepted and is now being reviewed.',
            'Missing Documents' => 'Your request requires additional documents.',
            'Approved' => 'Your request has been approved!',
            'Rejected' => 'Your request has been rejected.',
            'Completed' => 'Your request has been completed!',
            default => 'Your request status has been updated to: ' . $this->newStatus,
        };

        $message = (new MailMessage)
            ->greeting('Request Status Update')
            ->line('Service Request #' . $this->serviceRequest->id)
            ->line('Service: ' . $this->serviceRequest->service->name)
            ->line($statusMessage);

        if ($this->notes) {
            $message->line('Notes: ' . $this->notes);
        }

        $message->action('View Request', route('citizen.service-requests.show', $this->serviceRequest))
            ->line('Thank you for using the E-Services Platform.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'service_request_id' => $this->serviceRequest->id,
            'service_name' => $this->serviceRequest->service->name,
            'status' => $this->newStatus,
            'notes' => $this->notes,
        ];
    }
}
