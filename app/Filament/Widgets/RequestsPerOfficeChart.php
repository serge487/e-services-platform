<?php

namespace App\Filament\Widgets;

use App\Filament\Support\AdminDashboardFilters;
use App\Models\Office;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class RequestsPerOfficeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Requests per Office';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $officeIds = AdminDashboardFilters::officeIds($this->filters);

        $offices = Office::withCount(['services as requests_count' => function ($query) {
            $query->join('service_requests', 'services.id', '=', 'service_requests.service_id');
        }])
            ->when($officeIds !== null, fn (Builder $query) => $query->whereIn('id', $officeIds))
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Requests',
                    'data'            => $offices->pluck('requests_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#f59e0b', '#10b981', '#ef4444',
                        '#8b5cf6', '#06b6d4', '#f97316', '#84cc16',
                    ],
                ],
            ],
            'labels' => $offices->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
