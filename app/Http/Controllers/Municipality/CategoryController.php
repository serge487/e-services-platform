<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Store a new category under one of the authenticated user's offices.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id' => ['required', 'integer'],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        $this->authorizeOfficeAccess($validated['office_id']);

        Category::create([
            'office_id' => $validated['office_id'],
            'name'      => $validated['name'],
        ]);

        return back()->with('success', 'Category "' . $validated['name'] . '" created successfully.');
    }

    /**
     * Update an existing category's name.
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeOfficeAccess($category->office_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update(['name' => $validated['name']]);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category (cascades to services via DB constraint).
     */
    public function destroy(Category $category)
    {
        $this->authorizeOfficeAccess($category->office_id);

        $categoryName = $category->name;
        $category->delete();

        return back()->with('success', 'Category "' . $categoryName . '" deleted.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

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
}