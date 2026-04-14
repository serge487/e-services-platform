<?php

namespace App\Http\Controllers\Citizen;

use App\Events\AppointmentChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);

        $availableSlots = $this->availableSlotsQuery()->paginate(12, ['*'], 'available_page');
        $appointments = $this->appointmentsQuery($citizen->id)->paginate(12, ['*'], 'my_page');

        return view('citizen.appointments', compact('availableSlots', 'appointments'));
    }

    public function live(Request $request)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);

        $availableSlots = $this->availableSlotsQuery()->paginate(12, ['*'], 'available_page');
        $appointments = $this->appointmentsQuery($citizen->id)->paginate(12, ['*'], 'my_page');

        return response()->json([
            'available_slots_html' => view('citizen.partials.appointments-available-slots', compact('availableSlots'))->render(),
            'appointments_html' => view('citizen.partials.appointments-my-bookings', compact('appointments'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);

        $validated = $request->validate([
            'officer_time_slot_id' => ['required', 'integer', 'exists:officer_time_slots,id'],
        ]);

        DB::transaction(function () use ($validated, $citizen): int {
            $slot = OfficerTimeSlot::query()
                ->with('office')
                ->lockForUpdate()
                ->findOrFail($validated['officer_time_slot_id']);

            if ($slot->is_booked) {
                abort(422, 'This slot was already booked. Please choose another one.');
            }

            $startsAt = Carbon::parse($slot->slot_date->format('Y-m-d').' '.$slot->start_time->format('H:i:s'));
            if ($startsAt->lessThanOrEqualTo(Carbon::now())) {
                abort(422, 'This slot is already in the past. Please choose another one.');
            }

            $appointment = Appointment::create([
                'service_request_id' => null,
                'officer_time_slot_id' => $slot->id,
                'citizen_id' => $citizen->id,
                'status' => 'scheduled',
            ]);

            $slot->update(['is_booked' => true]);
            $this->broadcastAppointmentChanged($slot, (int) $citizen->id, (int) $appointment->id);

            return (int) $appointment->id;
        });

        return back()->with('success', 'Appointment booked successfully.');
    }

    public function cancel(Appointment $appointment)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);
        abort_unless((int) $appointment->citizen_id === (int) $citizen->id, 403);

        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            return back()->with('warning', 'Only scheduled appointments can be cancelled.');
        }

        DB::transaction(function () use ($appointment): void {
            $appointment->update(['status' => 'cancelled']);
            $appointment->officerTimeSlot?->update(['is_booked' => false]);
            if ($appointment->officerTimeSlot) {
                $this->broadcastAppointmentChanged($appointment->officerTimeSlot, (int) $appointment->citizen_id, (int) $appointment->id);
            }
        });

        return back()->with('success', 'Appointment cancelled.');
    }

    private function broadcastAppointmentChanged(OfficerTimeSlot $slot, int $citizenId, int $appointmentId): void
    {
        $slot->loadMissing('office');
        $municipalityId = (int) $slot->office->municipality_id;
        $officeId = (int) $slot->office_id;

        $recipientIds = User::query()
            ->where(function ($query) use ($municipalityId, $officeId): void {
                $query->where(function ($sub) use ($municipalityId): void {
                    $sub->where('role', 'municipality')
                        ->where('municipality_id', $municipalityId);
                })->orWhere(function ($sub) use ($officeId): void {
                    $sub->where('role', 'office_staff')
                        ->where('office_id', $officeId);
                });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $recipientIds[] = $citizenId;

        foreach (array_unique($recipientIds) as $userId) {
            broadcast(new AppointmentChanged((int) $userId, $appointmentId));
        }
    }

    private function availableSlotsQuery()
    {
        return OfficerTimeSlot::query()
            ->with(['office', 'officer'])
            ->where('is_booked', false)
            ->where(function ($query): void {
                $query->whereDate('slot_date', '>', now()->toDateString())
                    ->orWhere(function ($subQuery): void {
                        $subQuery->whereDate('slot_date', now()->toDateString())
                            ->where('start_time', '>', now()->format('H:i:s'));
                    });
            })
            ->orderBy('slot_date')
            ->orderBy('start_time');
    }

    private function appointmentsQuery(int $citizenId)
    {
        return Appointment::query()
            ->with(['officerTimeSlot.office', 'officerTimeSlot.officer'])
            ->where('citizen_id', $citizenId)
            ->where(function ($query): void {
                $query->where('status', '!=', 'cancelled')
                    ->orWhereHas('officerTimeSlot', function ($slotQuery): void {
                        $slotQuery->whereDate('slot_date', '>', now()->toDateString())
                            ->orWhere(function ($todayQuery): void {
                                $todayQuery->whereDate('slot_date', now()->toDateString())
                                    ->where('start_time', '>', now()->format('H:i:s'));
                            });
                    });
            })
            ->latest();
    }
}

