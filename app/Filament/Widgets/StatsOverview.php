<?php

namespace App\Filament\Widgets;

use App\Filament\Support\AdminDashboardFilters;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RevenueService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected $listeners = ['revenue-updated' => '$refresh'];

    protected function getStats(): array
    {
        $officeIds = AdminDashboardFilters::officeIds($this->filters);

        $requestsQuery = ServiceRequest::query();
        if ($officeIds !== null) {
            $requestsQuery->whereHas('service', fn ($q) => $q->whereIn('office_id', $officeIds));
        }

        $totalRequests = (clone $requestsQuery)->count();
        $pendingRequests = (clone $requestsQuery)->where('status', 'Pending')->count();

        return [
            Stat::make('Total Requests', $totalRequests)
                ->description('All service requests')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Pending Requests', $pendingRequests)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Revenue', RevenueService::formattedTotal($officeIds))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Total Citizens', User::where('role', 'citizen')->count())
                ->description('Registered citizens')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
        ];
    }
}
