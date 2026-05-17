<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * List all services grouped by office → category for this municipality.
     */
    public function index()
    {
        $offices = $this->getMunicipalityOffices();

        // Eager-load categories and their services
        $offices->load(['categories.services']);

        return view('municipality.services', compact('offices'));
    }

    /**
     * Show the create service form.
     */
    public function create()
    {
        $offices = $this->getMunicipalityOffices();
        $offices->load('categories');

        return view('municipality.services-create', compact('offices'));
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id'          => ['required', 'integer'],
            'category_id'        => ['required', 'integer'],
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'price'              => ['required', 'numeric', 'min:0'],
            'duration_days'      => ['required', 'integer', 'min:1'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:255'],
        ]);

        $this->authorizeOfficeAccess($validated['office_id']);
        $this->authorizeCategoryBelongsToOffice($validated['category_id'], $validated['office_id']);

        Service::create([
            'office_id'          => $validated['office_id'],
            'category_id'        => $validated['category_id'],
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'price'              => $validated['price'],
            'duration_days'      => $validated['duration_days'],
            'required_documents' => array_values(
                array_filter($validated['required_documents'] ?? [], fn($doc) => trim($doc) !== '')
            ),
        ]);

        return redirect()->route('municipality.services')
            ->with('success', 'Service "' . $validated['name'] . '" created successfully.');
    }

    /**
     * Show the edit form for a specific service.
     */
    public function edit(Service $service)
    {
        $this->authorizeOfficeAccess($service->office_id);

        $offices = $this->getMunicipalityOffices();
        $offices->load('categories');

        return view('municipality.services-edit', compact('service', 'offices'));
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service)
    {
        $this->authorizeOfficeAccess($service->office_id);

        $validated = $request->validate([
            'office_id'            => ['required', 'integer'],
            'category_id'          => ['required', 'integer'],
            'name'                 => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'price'                => ['required', 'numeric', 'min:0'],
            'duration_days'        => ['required', 'integer', 'min:1'],
            'required_documents'   => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:255'],
        ]);

        $this->authorizeOfficeAccess($validated['office_id']);
        $this->authorizeCategoryBelongsToOffice($validated['category_id'], $validated['office_id']);

        $service->update([
            'office_id'          => $validated['office_id'],
            'category_id'        => $validated['category_id'],
            'name'               => $validated['name'],
            'description'        => $validated['description'] ?? null,
            'price'              => $validated['price'],
            'duration_days'      => $validated['duration_days'],
            'required_documents' => array_values(
                array_filter($validated['required_documents'] ?? [], fn($doc) => trim($doc) !== '')
            ),
        ]);

        return redirect()->route('municipality.services')
            ->with('success', 'Service "' . $service->name . '" updated successfully.');
    }

    /**
     * Delete a service.
     */
    public function destroy(Service $service)
    {
        $this->authorizeOfficeAccess($service->office_id);

        $serviceName = $service->name;
        $service->delete();

        return back()->with('success', 'Service "' . $serviceName . '" deleted.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Get all offices belonging to the authenticated user's municipality.
     */
    private function getMunicipalityOffices()
    {
        $municipality = Auth::user()->municipality;

        if (! $municipality) {
            abort(403, 'No municipality assigned to your account.');
        }

        return $municipality->offices()->orderBy('name')->get();
    }

    /**
     * Abort 403 if the office does not belong to the current user's municipality.
     */
    private function authorizeOfficeAccess(int $officeId): void
    {
        $municipalityId = Auth::user()->municipality_id;

        $officeExists = Office::where('id', $officeId)
            ->where('municipality_id', $municipalityId)
            ->exists();

        if (! $officeExists) {
            abort(403, 'You do not have permission to manage this office.');
        }
    }

    /**
     * Abort 403 if the category does not belong to the given office.
     */
    private function authorizeCategoryBelongsToOffice(int $categoryId, int $officeId): void
    {
        $valid = Category::where('id', $categoryId)
            ->where('office_id', $officeId)
            ->exists();

        if (! $valid) {
            abort(403, 'The selected category does not belong to this office.');
        }
    }
}