<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AppointmentReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly Appointment $appointment) {}

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

        return (new MailMessage)
            ->subject('Appointment Reminder')
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('This is a reminder about your upcoming appointment:')
            ->line('**Office:** '.($slot->office->name ?? '-'))
            ->line('**Officer:** '.($slot->officer->name ?? '-'))
            ->line('**Date:** '.$slotDate)
            ->line('**Time:** '.$startTime.' – '.$endTime)
            ->action('View My Appointments', route('citizen.appointments', absolute: false))
            ->line('Please arrive on time. Contact the municipality if you need to reschedule.')
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

        return [
            'type' => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'office_name' => $slot->office?->name,
            'officer_name' => $slot->officer?->name,
            'slot_date' => $slot->slot_date,
            'start_time' => $slot->start_time,
            'sender_name' => $slot->office?->name ?? 'Municipality',
            'preview' => 'Appointment reminder: '.($slot->office?->name ?? 'Office')
                .' on '.$slotDate.' at '.$startTime,
        ];
    }
}
