<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use App\Models\Office;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Browse all services, optionally filtered by office and category.
     */
    public function index(Request $request)
    {
        $officeFilter = $request->query('office_id');
        $categoryFilter = $request->query('category_id');

        $query = Service::with(['office', 'category']);

        if ($officeFilter) {
            $query->where('office_id', $officeFilter);
        }

        if ($categoryFilter) {
            $query->where('category_id', $categoryFilter);
        }

        $services = $query->paginate(12);

        // Load all offices and categories for filter dropdowns
        $offices = Office::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('citizen.services', compact('services', 'offices', 'categories', 'officeFilter', 'categoryFilter'));
    }

    /**
     * Show service details and request form.
     */
    public function show(Service $service)
    {
        $service->load(['office', 'category', 'feedbacks.citizen']);

        return view('citizen.service-detail', compact('service'));
    }
}
