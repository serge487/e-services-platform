<?php

namespace App\Filament\Widgets;

use App\Filament\Support\AdminDashboardFilters;
use App\Services\RevenueService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';

    protected $listeners = ['revenue-updated' => '$refresh'];

    protected function getData(): array
    {
        $officeIds = AdminDashboardFilters::officeIds($this->filters);
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));
        $revenue = collect(RevenueService::monthlyTotals(5, $officeIds));

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (USD)',
                    'data'            => $revenue->toArray(),
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
