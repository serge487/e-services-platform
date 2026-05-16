<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ServiceRequest;
use App\Services\RevenueService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $officeIds = Auth::user()->accessibleOfficeIds();

        $stats = $this->buildStats($officeIds);
        $todayAppointments = $this->todayAppointments($officeIds);
        $pendingRequests = $this->pendingRequests($officeIds);

        return view('municipality.dashboard', compact('stats', 'todayAppointments', 'pendingRequests'));
    }

    public function live()
    {
        $officeIds = Auth::user()->accessibleOfficeIds();

        $stats = $this->buildStats($officeIds);
        $todayAppointments = $this->todayAppointments($officeIds);

        return response()->json([
            'stats' => $stats,
            'today_appointments_html' => view('municipality.partials.dashboard-today-appointments', compact('todayAppointments'))->render(),
        ]);
    }

    private function buildStats(array $officeIds): array
    {
        $totalRequests = ServiceRequest::query()
            ->whereHas('service', fn ($q) => $q->whereIn('office_id', $officeIds))
            ->count();

        $pendingRequests = ServiceRequest::query()
            ->whereHas('service', fn ($q) => $q->whereIn('office_id', $officeIds))
            ->where('status', 'Pending')
            ->count();

        $appointmentsToday = Appointment::query()
            ->whereHas('officerTimeSlot', function ($q) use ($officeIds): void {
                $q->whereIn('office_id', $officeIds)
                    ->whereDate('slot_date', now()->toDateString());
            })
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        $unreadMessages = Auth::user()->unreadNotifications()->count();
        $totalRevenue = RevenueService::formattedTotal($officeIds);

        return [
            'total_requests' => $totalRequests,
            'pending' => $pendingRequests,
            'appointments_today' => $appointmentsToday,
            'unread_messages' => $unreadMessages,
            'total_revenue' => $totalRevenue,
        ];
    }

    private function todayAppointments(array $officeIds)
    {
        return Appointment::query()
            ->with(['citizen', 'officerTimeSlot.office', 'officerTimeSlot.officer'])
            ->whereHas('officerTimeSlot', function ($q) use ($officeIds): void {
                $q->whereIn('office_id', $officeIds)
                    ->whereDate('slot_date', now()->toDateString());
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    private function pendingRequests(array $officeIds)
    {
        return ServiceRequest::query()
            ->with(['citizen', 'service.office', 'service.category', 'requestDocuments'])
            ->whereHas('service', fn ($q) => $q->whereIn('office_id', $officeIds))
            ->whereIn('status', ['Pending', 'In Review', 'Missing Documents'])
            ->latest()
            ->limit(8)
            ->get();
    }
}