<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Office;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalRequests = ServiceRequest::count();
        $pendingRequests = ServiceRequest::where('status', 'Pending')->count();
        $totalCitizens = User::where('role', 'citizen')->count();

        return [
            Stat::make('Total Requests', $totalRequests)
                ->description('All service requests')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Pending Requests', $pendingRequests)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From completed payments')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Total Citizens', $totalCitizens)
                ->description('Registered citizens')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
        ];
    }
}