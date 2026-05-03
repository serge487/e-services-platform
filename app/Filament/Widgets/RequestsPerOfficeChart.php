<?php

namespace App\Filament\Widgets;

use App\Models\Municipality;
use App\Models\Office;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class RequestsPerOfficeChart extends ChartWidget
{
    protected static ?string $heading = 'Requests per Office';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    // Holds the selected municipality ID
    public ?string $municipalityId = null;

    // Dropdown filter shown on the widget
    protected function getFilters(): ?array
    {
        $municipalities = Municipality::orderBy('name')->pluck('name', 'id')->toArray();

        return ['' => 'All Municipalities'] + $municipalities;
    }

    protected function getData(): array
    {
        $offices = Office::withCount(['services as requests_count' => function ($query) {
                $query->join('service_requests', 'services.id', '=', 'service_requests.service_id');
            }])
            ->when($this->filter, function (Builder $query) {
                $query->where('municipality_id', $this->filter);
            })
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