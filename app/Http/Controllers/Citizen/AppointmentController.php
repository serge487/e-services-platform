<?php

namespace App\Http\Controllers\Citizen;

use App\Events\AppointmentChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);

        $officesForSlotFilter = $this->officesWithAvailableSlots();
        [$availableSlots, $appointments] = $this->paginatedAppointmentsData($request, (int) $citizen->id);

        return view('citizen.appointments', compact('availableSlots', 'appointments', 'officesForSlotFilter'));
    }

    public function live(Request $request)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);

        [$availableSlots, $appointments] = $this->paginatedAppointmentsData($request, (int) $citizen->id);

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
            'slot_office_id' => ['nullable', 'integer'],
            'slot_date' => ['nullable', 'string', 'max:32'],
            'slot_q' => ['nullable', 'string', 'max:120'],
            'booking_status' => ['nullable', 'string', 'max:32'],
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

        return redirect()
            ->route('citizen.appointments', $this->appointmentFilterQuery($request))
            ->with('success', 'Appointment booked successfully.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $citizen = Auth::user();
        abort_unless($citizen->role === 'citizen', 403);
        abort_unless((int) $appointment->citizen_id === (int) $citizen->id, 403);

        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            return redirect()
                ->route('citizen.appointments', $this->appointmentFilterQuery($request))
                ->with('warning', 'Only scheduled appointments can be cancelled.');
        }

        DB::transaction(function () use ($appointment): void {
            $appointment->update(['status' => 'cancelled']);
            $appointment->officerTimeSlot?->update(['is_booked' => false]);
            if ($appointment->officerTimeSlot) {
                $this->broadcastAppointmentChanged($appointment->officerTimeSlot, (int) $appointment->citizen_id, (int) $appointment->id);
            }
        });

        return redirect()
            ->route('citizen.appointments', $this->appointmentFilterQuery($request))
            ->with('success', 'Appointment cancelled.');
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

    /**
     * Pagination links must target the HTML page, not this JSON "live" endpoint,
     * otherwise clicking page 2+ opens raw JSON in the browser.
     *
     * @return array{0: LengthAwarePaginator, 1: LengthAwarePaginator}
     */
    private function paginatedAppointmentsData(Request $request, int $citizenId): array
    {
        $pagePath = route('citizen.appointments', [], false);

        $availableSlots = $this->availableSlotsQuery($request)->paginate(12, ['*'], 'available_page');
        $availableSlots->withPath($pagePath);

        $appointments = $this->appointmentsQuery($request, $citizenId)->paginate(12, ['*'], 'my_page');
        $appointments->withPath($pagePath);

        return [$availableSlots, $appointments];
    }

    /**
     * Future, unbooked slots (no citizen filters) — used to populate office dropdown.
     *
     * @return Builder<OfficerTimeSlot>
     */
    private function baseFutureAvailableSlotsQuery()
    {
        return OfficerTimeSlot::query()
            ->with(['office.municipality', 'officer'])
            ->where('is_booked', false)
            ->where(function ($query): void {
                $query->whereDate('slot_date', '>', now()->toDateString())
                    ->orWhere(function ($subQuery): void {
                        $subQuery->whereDate('slot_date', now()->toDateString())
                            ->where('start_time', '>', now()->format('H:i:s'));
                    });
            });
    }

    /**
     * @return Collection<int, Office>
     */
    private function officesWithAvailableSlots()
    {
        $ids = $this->baseFutureAvailableSlotsQuery()
            ->clone()
            ->select('office_id')
            ->distinct()
            ->pluck('office_id');

        return Office::query()
            ->with('municipality')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Builder<OfficerTimeSlot>
     */
    private function availableSlotsQuery(Request $request)
    {
        $query = $this->baseFutureAvailableSlotsQuery()->clone();

        if ($request->filled('slot_office_id') && is_numeric($request->input('slot_office_id'))) {
            $query->where('office_id', (int) $request->input('slot_office_id'));
        }

        if ($request->filled('slot_date')) {
            try {
                $query->whereDate('slot_date', Carbon::parse($request->input('slot_date'))->toDateString());
            } catch (\Throwable) {
                // ignore invalid date
            }
        }

        if ($request->filled('slot_q')) {
            $raw = trim((string) $request->input('slot_q'));
            if ($raw !== '') {
                $like = '%'.addcslashes($raw, '%_\\').'%';
                $query->where(function ($sub) use ($like): void {
                    $sub->whereHas('office', fn ($o) => $o->where('name', 'like', $like))
                        ->orWhereHas('officer', fn ($u) => $u->where('name', 'like', $like));
                });
            }
        }

        return $query->orderBy('slot_date')->orderBy('start_time');
    }

    /**
     * @return Builder<Appointment>
     */
    private function appointmentsQuery(Request $request, int $citizenId)
    {
        $query = Appointment::query()
            ->with(['officerTimeSlot.office', 'officerTimeSlot.officer'])
            ->where('citizen_id', $citizenId);

        if ($request->filled('booking_status')) {
            $status = (string) $request->input('booking_status');
            if (in_array($status, ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'], true)) {
                return $query->where('status', $status)->latest();
            }
        }

        return $query
            ->where(function ($q): void {
                $q->where('status', '!=', 'cancelled')
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

    /**
     * @return array<string, mixed>
     */
    private function appointmentFilterQuery(Request $request): array
    {
        $keys = ['slot_office_id', 'slot_date', 'slot_q', 'booking_status', 'available_page', 'my_page'];
        $out = [];
        foreach ($keys as $key) {
            if (! $request->has($key)) {
                continue;
            }
            $value = $request->input($key);
            if ($value === null || $value === '') {
                continue;
            }
            $out[$key] = $value;
        }

        return $out;
    }
}
