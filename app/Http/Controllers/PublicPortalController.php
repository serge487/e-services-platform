<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicPortalController extends Controller
{
    /**
     * Public portal home — shows map + office list.
     * Non-citizen authenticated users are redirected to their area.
     * Citizens stay on the portal with their personal stats unlocked.
     */
    public function index(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Admin → Filament panel
            if ($user->role === 'admin') {
                return redirect('/admin');
            }

            // Municipality staff → municipality dashboard
            if (in_array($user->role, ['municipality', 'office_staff'])) {
                return redirect()->route('municipality.dashboard');
            }

            // Citizen not yet identity-verified → verification flow
            if (
                $user->role === 'citizen'
                && ! $user->identity_verified_at
                && ! config('citizen.skip_identity_verification_gate')
            ) {
                return redirect()->route('citizen.identity-verification.show');
            }

            // Verified citizens fall through and see the portal with stats
        }

        // Load all offices with coordinates for the map
        $offices = Office::with(['municipality', 'categories.services'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        // JSON-safe data for Leaflet markers
        $officesForMap = $offices->map(fn ($office) => [
            'id'        => $office->id,
            'name'      => $office->name,
            'address'   => $office->address,
            'latitude'  => (float) $office->latitude,
            'longitude' => (float) $office->longitude,
            'url'       => route('portal.office', $office, absolute: false),
        ]);

        $searchQuery = $request->query('search', '');

        // Filter list by search — map always shows all pins
        $filteredOffices = $searchQuery
            ? $offices->filter(fn ($office) =>
                str_contains(strtolower($office->name), strtolower($searchQuery)) ||
                str_contains(strtolower($office->address), strtolower($searchQuery)) ||
                str_contains(strtolower($office->municipality->name ?? ''), strtolower($searchQuery))
              )->values()
            : $offices;

        // Citizen personal stats — only loaded when a citizen is logged in
        $citizenStats = $this->loadCitizenStats();

        return view('public.portal', compact(
            'offices',
            'filteredOffices',
            'officesForMap',
            'searchQuery',
            'citizenStats'
        ));
    }

    /**
     * Public office detail page — shows office info + services.
     * No authentication required. Citizens see "Request" button, guests see "Login to Request".
     */
    public function show(Office $office)
    {
        $office->load(['municipality', 'categories.services']);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $workingHours = $this->normalizeWorkingHours($office->working_hours, $days);

        $citizenStats = $this->loadCitizenStats();

        return view('public.office-detail', compact(
            'office',
            'workingHours',
            'days',
            'citizenStats'
        ));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Load citizen-specific stats for the navbar pills.
     * Returns null if no citizen is logged in.
     */
    private function loadCitizenStats(): ?array
    {
        if (! Auth::check() || Auth::user()->role !== 'citizen') {
            return null;
        }

        $citizen = Auth::user();

        return [
            'active_requests'       => $citizen->serviceRequests()
                                        ->whereNotIn('status', ['Completed', 'Rejected'])
                                        ->count(),
            'upcoming_appointments' => $citizen->appointments()
                                        ->where('status', 'scheduled')
                                        ->count(),
            'unread_notifications'  => $citizen->unreadNotifications()->count(),
        ];
    }

    /**
     * Normalize working hours — ensures all 7 days are present with defaults.
     */
    private function normalizeWorkingHours(?array $workingHours, array $days): array
    {
        $normalized = [];

        foreach ($days as $day) {
            $normalized[$day] = [
                'is_open'    => $workingHours[$day]['is_open']    ?? false,
                'open_time'  => $workingHours[$day]['open_time']  ?? '08:00',
                'close_time' => $workingHours[$day]['close_time'] ?? '16:00',
            ];
        }

        return $normalized;
    }
}