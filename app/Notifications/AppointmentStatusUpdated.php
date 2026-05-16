<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AppointmentStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
        private readonly string $previousStatus,
        private readonly string $newStatus,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing([
            'officerTimeSlot.office',
            'officerTimeSlot.officer',
        ]);

        $slot = $this->appointment->officerTimeSlot;
        $slotDate = Carbon::parse($slot->slot_date)->format('l, F j, Y');
        $startTime = Carbon::parse($slot->start_time)->format('g:i A');
        $endTime = Carbon::parse($slot->end_time)->format('g:i A');

        $statusMessage = match ($this->newStatus) {
            'confirmed' => 'Your appointment has been confirmed by the municipality.',
            'completed' => 'Your appointment has been marked as completed.',
            'cancelled' => 'Your appointment has been cancelled.',
            'no_show' => 'Your appointment was marked as a no-show.',
            'scheduled' => 'Your appointment is scheduled.',
            default => 'Your appointment status is now: '.str_replace('_', ' ', ucfirst($this->newStatus)).'.',
        };

        $statusLabel = str_replace('_', ' ', ucfirst($this->newStatus));

        return (new MailMessage)
            ->subject('Appointment '.$statusLabel)
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line($statusMessage)
            ->line('**Office:** '.($slot->office->name ?? '-'))
            ->line('**Officer:** '.($slot->officer->name ?? '-'))
            ->line('**Date:** '.$slotDate)
            ->line('**Time:** '.$startTime.' – '.$endTime)
            ->action('View My Appointments', route('citizen.appointments', absolute: false))
            ->salutation('E-Services Portal');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing(['officerTimeSlot.office', 'officerTimeSlot.officer']);
        $slot = $this->appointment->officerTimeSlot;

        $slotDate = Carbon::parse($slot->slot_date)->format('M j, Y');
        $startTime = Carbon::parse($slot->start_time)->format('H:i');
        $statusLabel = str_replace('_', ' ', ucfirst($this->newStatus));

        return [
            'type' => 'appointment_status',
            'appointment_id' => $this->appointment->id,
            'previous_status' => $this->previousStatus,
            'status' => $this->newStatus,
            'office_name' => $slot->office?->name,
            'officer_name' => $slot->officer?->name,
            'sender_name' => $slot->office?->name ?? 'Municipality',
            'preview' => 'Appointment '.$statusLabel.': '.($slot->office?->name ?? 'Office')
                .' on '.$slotDate.' at '.$startTime,
        ];
    }
}
