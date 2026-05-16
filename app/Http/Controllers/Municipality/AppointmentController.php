<?php

namespace App\Http\Controllers\Municipality;

use App\Events\AppointmentChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use App\Notifications\AppointmentReminder;
use App\Notifications\AppointmentStatusUpdated;
use App\Services\NotificationRealtimeBroadcaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    private const MANAGEABLE_STATUSES = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'];

    public function index(Request $request)
    {
        $officeIds = $this->getAccessibleOfficeIds();
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $pagePath = route('municipality.appointments', [], false);
        $appointments = $this->appointmentsQuery($officeIds, $statusFilter, $search)->paginate(20, ['*'], 'appointments_page');
        $appointments->withPath($pagePath);
        $timeSlots = $this->timeSlotsQuery($officeIds)->paginate(20, ['*'], 'slots_page');
        $timeSlots->withPath($pagePath);

        $offices = Office::query()->whereIn('id', $officeIds)->orderBy('name')->get();
        $officers = User::query()
            ->where('role', 'office_staff')
            ->whereIn('office_id', $officeIds)
            ->orderBy('name')
            ->get();

        return view('municipality.appointments', compact(
            'appointments',
            'timeSlots',
            'offices',
            'officers',
            'statusFilter',
            'search'
        ));
    }

    public function live(Request $request)
    {
        $officeIds = $this->getAccessibleOfficeIds();
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $pagePath = route('municipality.appointments', [], false);
        $appointments = $this->appointmentsQuery($officeIds, $statusFilter, $search)->paginate(20, ['*'], 'appointments_page');
        $appointments->withPath($pagePath);
        $timeSlots = $this->timeSlotsQuery($officeIds)->paginate(20, ['*'], 'slots_page');
        $timeSlots->withPath($pagePath);

        return response()->json([
            'time_slots_html' => view('municipality.partials.appointments-time-slots', compact('timeSlots'))->render(),
            'booked_appointments_html' => view('municipality.partials.appointments-booked-table', compact('appointments', 'statusFilter', 'search'))->render(),
        ]);
    }

    public function storeSlot(Request $request)
    {
        $officeIds = $this->getAccessibleOfficeIds();

        $validated = $request->validate([
            'office_id' => ['required', 'integer', 'in:'.implode(',', $officeIds)],
            'officer_id' => ['required', 'integer', 'exists:users,id'],
            'slot_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $validated['slot_date'].' '.$validated['start_time']);
        if ($startsAt->lessThanOrEqualTo(Carbon::now())) {
            return back()->with('warning', 'Start time must be in the future for slots created today.');
        }

        $officerBelongsToOffice = User::query()
            ->where('id', $validated['officer_id'])
            ->where('role', 'office_staff')
            ->where('office_id', $validated['office_id'])
            ->exists();

        if (! $officerBelongsToOffice) {
            return back()->with('warning', 'Selected officer does not belong to this office.');
        }

        $existingSameSlot = OfficerTimeSlot::query()
            ->with('appointment')
            ->where('officer_id', $validated['officer_id'])
            ->whereDate('slot_date', $validated['slot_date'])
            ->whereTime('start_time', $validated['start_time'])
            ->whereTime('end_time', $validated['end_time'])
            ->first();

        if ($existingSameSlot) {
            if (! $existingSameSlot->is_booked) {
                return back()->with('success', 'This time slot already exists and is available.');
            }

            if ($existingSameSlot->appointment?->status === 'cancelled') {
                $existingSameSlot->update(['is_booked' => false]);

                return back()->with('success', 'Cancelled slot was reopened and is available again.');
            }
        }

        $hasOverlap = OfficerTimeSlot::query()
            ->where('officer_id', $validated['officer_id'])
            ->whereDate('slot_date', $validated['slot_date'])
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->where(function ($query): void {
                $query->whereDoesntHave('appointment')
                    ->orWhereHas('appointment', function ($appointmentQuery): void {
                        $appointmentQuery->where('status', '!=', 'cancelled');
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return back()->with('warning', 'This officer already has an overlapping slot.');
        }

        OfficerTimeSlot::create([
            'office_id' => $validated['office_id'],
            'officer_id' => $validated['officer_id'],
            'slot_date' => $validated['slot_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_booked' => false,
        ]);

        $this->broadcastAppointmentChangedForOffice((int) $validated['office_id']);

        return back()->with('success', 'Time slot added successfully.');
    }

    public function destroySlot(OfficerTimeSlot $officerTimeSlot)
    {
        $this->authorizeSlotAccess($officerTimeSlot);

        if ($officerTimeSlot->is_booked) {
            return back()->with('warning', 'Booked slots cannot be deleted.');
        }

        $officerTimeSlot->delete();
        $this->broadcastAppointmentChangedForOffice((int) $officerTimeSlot->office_id);

        return back()->with('success', 'Time slot deleted.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', self::MANAGEABLE_STATUSES)],
        ]);

        $previousStatus = $appointment->status;

        $appointment->update(['status' => $validated['status']]);

        if ($validated['status'] === 'cancelled') {
            $appointment->officerTimeSlot?->update(['is_booked' => false]);
        }

        $this->broadcastAppointmentChangedForAppointment($appointment);

        if ($previousStatus !== $validated['status']) {
            try {
                $this->notifyCitizenAboutAppointment(
                    $appointment,
                    new AppointmentStatusUpdated($appointment, $previousStatus, $validated['status'])
                );
            } catch (\Throwable $exception) {
                logger()->error('Appointment status notification failed', [
                    'appointment_id' => $appointment->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $validated['status'],
                    'error' => $exception->getMessage(),
                ]);

                return back()->with(
                    'warning',
                    'Status updated, but the citizen could not be notified by email.'
                );
            }

            return back()->with('success', 'Appointment status updated and citizen notified by email.');
        }

        return back()->with('success', 'Appointment status updated.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);
        $appointment->loadMissing('officerTimeSlot');

        $slot = $appointment->officerTimeSlot;
        if ($slot) {
            $slot->update(['is_booked' => false]);
        }

        $appointment->delete();

        if ($slot) {
            $this->broadcastAppointmentChangedForOffice((int) $slot->office_id);
        }

        return back()->with('success', 'Appointment deleted successfully.');
    }

    public function sendReminder(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointmentAccess($appointment);

        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            $message = 'Reminder can only be sent for scheduled or confirmed appointments.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('warning', $message);
        }

        $appointment->loadMissing(['citizen', 'officerTimeSlot.office', 'officerTimeSlot.officer']);
        $citizen = $appointment->citizen;

        try {
            $this->notifyCitizenAboutAppointment($appointment, new AppointmentReminder($appointment));
        } catch (\Throwable $exception) {
            logger()->error('Appointment reminder failed', [
                'appointment_id' => $appointment->id,
                'citizen_id' => $citizen->id,
                'error' => $exception->getMessage(),
            ]);

            $message = $this->mailFailureMessage($exception);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return back()->with('warning', $message);
        }

        $message = 'Reminder email sent to '.$citizen->email.'.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function notifyCitizenAboutAppointment(Appointment $appointment, Notification $notification): void
    {
        $appointment->loadMissing(['citizen', 'officerTimeSlot.office', 'officerTimeSlot.officer']);
        $citizen = $appointment->citizen;

        if (! $citizen?->email) {
            throw new \RuntimeException('Citizen does not have an email address.');
        }

        $citizen->notify($notification);
        NotificationRealtimeBroadcaster::broadcastLatest($citizen);
    }

    private function mailFailureMessage(\Throwable $exception): string
    {
        $error = $exception->getMessage();

        if (str_contains($error, 'does not have an email')) {
            return 'This citizen has no email address on file.';
        }

        if (str_contains($error, 'expired or revoked') || str_contains($error, 'invalid_grant')) {
            return 'Gmail token expired. Run `php artisan gmail:authorize`, update GMAIL_REFRESH_TOKEN, then `php artisan config:clear`.';
        }

        if (str_contains($error, 'not configured')) {
            return 'Gmail OAuth is not configured. Set GMAIL_CLIENT_ID, GMAIL_CLIENT_SECRET, and GMAIL_REFRESH_TOKEN in .env.';
        }

        return 'Could not send email. Check storage/logs/laravel.log for details.';
    }

    /**
     * @return list<int>
     */
    private function getAccessibleOfficeIds(): array
    {
        $ids = Auth::user()->accessibleOfficeIds();

        if ($ids === []) {
            abort(403, 'No office access is assigned to your account.');
        }

        return $ids;
    }

    private function authorizeSlotAccess(OfficerTimeSlot $slot): void
    {
        abort_unless(in_array((int) $slot->office_id, $this->getAccessibleOfficeIds(), true), 403);
    }

    private function authorizeAppointmentAccess(Appointment $appointment): void
    {
        $appointment->loadMissing('officerTimeSlot');
        $officeId = (int) $appointment->officerTimeSlot?->office_id;

        abort_unless(in_array($officeId, $this->getAccessibleOfficeIds(), true), 403);
    }

    private function broadcastAppointmentChangedForAppointment(Appointment $appointment): void
    {
        $appointment->loadMissing('officerTimeSlot.office');
        $officeId = (int) $appointment->officerTimeSlot->office_id;
        $municipalityId = (int) $appointment->officerTimeSlot->office->municipality_id;
        $citizenId = (int) $appointment->citizen_id;

        $this->broadcastToRecipients($officeId, $municipalityId, $citizenId, (int) $appointment->id);
    }

    private function broadcastAppointmentChangedForOffice(int $officeId): void
    {
        $office = Office::query()->find($officeId);
        if (! $office) {
            return;
        }

        $this->broadcastToRecipients($officeId, (int) $office->municipality_id, null, null);
    }

    private function broadcastToRecipients(int $officeId, int $municipalityId, ?int $citizenId, ?int $appointmentId): void
    {
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

        if ($citizenId) {
            $recipientIds[] = $citizenId;
        }

        foreach (array_unique($recipientIds) as $userId) {
            broadcast(new AppointmentChanged((int) $userId, $appointmentId));
        }
    }

    private function appointmentsQuery(array $officeIds, ?string $statusFilter, string $search = '')
    {
        return Appointment::query()
            ->with(['citizen', 'officerTimeSlot.office', 'officerTimeSlot.officer'])
            ->whereHas('officerTimeSlot', fn ($query) => $query->whereIn('office_id', $officeIds))
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $lettersOnly = preg_replace('/[^a-z0-9]/i', '', strtolower($search)) ?? '';
                $fuzzyLike = $lettersOnly !== '' ? '%'.implode('%', str_split($lettersOnly)).'%' : null;
                $compactLike = '%'.strtolower(str_replace(' ', '', $search)).'%';

                $query->whereHas('citizen', function ($citizenQuery) use ($like, $fuzzyLike, $compactLike): void {
                    $citizenQuery->where(function ($inner) use ($like, $fuzzyLike, $compactLike): void {
                        $inner->where('name', 'like', $like)
                            ->orWhere('phone_number', 'like', $like)
                            ->orWhereRaw('LOWER(REPLACE(name, " ", "")) LIKE ?', [$compactLike])
                            ->orWhereRaw('LOWER(REPLACE(phone_number, " ", "")) LIKE ?', [$compactLike]);

                        if ($fuzzyLike !== null) {
                            $inner->orWhereRaw('LOWER(REPLACE(name, " ", "")) LIKE ?', [$fuzzyLike])
                                ->orWhereRaw('LOWER(REPLACE(phone_number, " ", "")) LIKE ?', [$fuzzyLike]);
                        }
                    });
                });
            })
            ->latest();
    }

    private function timeSlotsQuery(array $officeIds)
    {
        return OfficerTimeSlot::query()
            ->with(['office', 'officer', 'appointment.citizen'])
            ->whereIn('office_id', $officeIds)
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
}
