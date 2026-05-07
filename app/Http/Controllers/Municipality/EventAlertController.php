<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\MunicipalityEvent;
use App\Models\Office;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\MunicipalityEventAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class EventAlertController extends Controller
{
    /**
     * List all events created by this municipality's offices.
     */
    public function index()
    {
        $officeIds = Auth::user()->accessibleOfficeIds();

        $events = MunicipalityEvent::with(['office', 'creator'])
            ->whereIn('office_id', $officeIds)
            ->latest()
            ->paginate(15);

        return view('municipality.event-alerts.index', compact('events'));
    }

    /**
     * Show the create event form.
     */
    public function create()
    {
        $officeIds = Auth::user()->accessibleOfficeIds();

        $offices = Office::whereIn('id', $officeIds)->orderBy('name')->get();

        // Preview: how many unique citizens would be notified per office
        $eligibleCounts = $this->getEligibleCountsPerOffice($officeIds);

        return view('municipality.event-alerts.create', compact('offices', 'eligibleCounts'));
    }

    /**
     * Store the event and dispatch notifications to eligible citizens.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id'   => ['required', 'integer'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'place'       => ['required', 'string', 'max:255'],
            'event_date'  => ['required', 'date', 'after:now'],
            'occasion'    => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure the office belongs to this municipality user
        $officeIds = Auth::user()->accessibleOfficeIds();
        abort_unless(in_array((int) $validated['office_id'], $officeIds), 403);

        // Collect eligible citizens (union of appointment + chat citizens)
        $eligibleCitizens = $this->getEligibleCitizens((int) $validated['office_id']);

        // Create the event record
        $event = MunicipalityEvent::create([
            'office_id'       => $validated['office_id'],
            'created_by'      => Auth::id(),
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'place'           => $validated['place'],
            'event_date'      => $validated['event_date'],
            'occasion'        => $validated['occasion'] ?? null,
            'notified_count'  => $eligibleCitizens->count(),
        ]);

        // Dispatch notifications — queued so they don't block the response
        Notification::send($eligibleCitizens, new MunicipalityEventAlert($event));

        return redirect()
            ->route('municipality.event-alerts.index')
            ->with('success', 'Event alert sent to ' . $eligibleCitizens->count() . ' citizen(s) successfully.');
    }

    /**
     * Show a single event's details.
     */
    public function show(MunicipalityEvent $eventAlert)
    {
        $officeIds = Auth::user()->accessibleOfficeIds();
        abort_unless(in_array($eventAlert->office_id, $officeIds), 403);

        $eventAlert->load(['office', 'creator']);

        return view('municipality.event-alerts.show', compact('eventAlert'));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Get all unique eligible citizens for a specific office.
     * Union of:
     *   - Citizens who booked appointments at this office
     *   - Citizens who chatted (sent messages on service requests) for this office
     */
    private function getEligibleCitizens(int $officeId)
    {
        // Citizens with appointments at this office
        $appointmentCitizenIds = Appointment::query()
            ->whereHas('officerTimeSlot', fn ($q) => $q->where('office_id', $officeId))
            ->pluck('citizen_id');

        // Citizens who sent messages on service requests belonging to this office
        $chatCitizenIds = Message::query()
            ->whereHas('serviceRequest.service', fn ($q) => $q->where('office_id', $officeId))
            ->whereHas('sender', fn ($q) => $q->where('role', 'citizen'))
            ->pluck('sender_id');

        // Union both sets, remove duplicates, fetch User models
        $uniqueCitizenIds = $appointmentCitizenIds
            ->merge($chatCitizenIds)
            ->unique()
            ->values();

        return User::whereIn('id', $uniqueCitizenIds)
            ->where('is_active', true)
            ->where('role', 'citizen')
            ->get();
    }

    /**
     * Get eligible citizen counts per office for the create form preview.
     */
    private function getEligibleCountsPerOffice(array $officeIds): array
    {
        $counts = [];

        foreach ($officeIds as $officeId) {
            $counts[$officeId] = $this->getEligibleCitizens($officeId)->count();
        }

        return $counts;
    }
}