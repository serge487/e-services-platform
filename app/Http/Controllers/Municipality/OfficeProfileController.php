<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeProfileController extends Controller
{
    /**
     * Display all offices belonging to the authenticated user's municipality.
     */
    public function index()
    {
        $municipality = Auth::user()->municipality;

        if (! $municipality) {
            abort(403, 'No municipality assigned to your account.');
        }

        $offices = $municipality->offices()->orderBy('name')->get();

        return view('municipality.office-profile', compact('offices', 'municipality'));
    }

    /**
     * Show the edit form for a specific office.
     * Ensures the office belongs to the authenticated user's municipality.
     */
    public function edit(Office $office)
    {
        $this->authorizeOfficeAccess($office);

        $workingHours = $this->normalizeWorkingHours($office->working_hours);

        return view('municipality.office-profile-edit', compact('office', 'workingHours'));
    }

    /**
     * Update a specific office's details.
     */
    public function update(Request $request, Office $office)
    {
        $this->authorizeOfficeAccess($office);

        $validated = $request->validate([
            'name'                              => ['required', 'string', 'max:255'],
            'address'                           => ['required', 'string', 'max:500'],
            'latitude'                          => ['required', 'numeric', 'between:-90,90'],
            'longitude'                         => ['required', 'numeric', 'between:-180,180'],
            'contact_info'                      => ['required', 'string', 'max:255'],
            'working_hours'                     => ['required', 'array'],
            'working_hours.*.is_open'           => ['boolean'],
            'working_hours.*.open_time'         => ['nullable', 'date_format:H:i'],
            'working_hours.*.close_time'        => ['nullable', 'date_format:H:i'],
        ]);

        // Build the structured working_hours JSON
        $workingHours = $this->buildWorkingHours($request->input('working_hours', []));

        $office->update([
            'name'          => $validated['name'],
            'address'       => $validated['address'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'contact_info'  => $validated['contact_info'],
            'working_hours' => $workingHours,
        ]);

        return redirect()
            ->route('municipality.office-profile')
            ->with('success', 'Office "' . $office->name . '" updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Abort with 403 if the office does not belong to the current user's municipality.
     */
    private function authorizeOfficeAccess(Office $office): void
    {
        $municipalityId = Auth::user()->municipality_id;

        if ($office->municipality_id !== $municipalityId) {
            abort(403, 'You do not have permission to manage this office.');
        }
    }

    /**
     * Ensure working_hours always has all 7 days with default values.
     * Handles cases where the DB value is null or incomplete.
     */
    private function normalizeWorkingHours(?array $workingHours): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

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

    /**
     * Build the working_hours array from raw form input.
     * Ensures closed days don't store stale open/close times.
     */
    private function buildWorkingHours(array $rawInput): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $workingHours = [];
        foreach ($days as $day) {
            $dayData  = $rawInput[$day] ?? [];
            $isOpen   = isset($dayData['is_open']) && $dayData['is_open'] == '1';

            $workingHours[$day] = [
                'is_open'    => $isOpen,
                'open_time'  => $isOpen ? ($dayData['open_time']  ?? '08:00') : null,
                'close_time' => $isOpen ? ($dayData['close_time'] ?? '16:00') : null,
            ];
        }

        return $workingHours;
    }
}