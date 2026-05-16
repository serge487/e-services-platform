<x-filament-panels::page class="fi-dashboard-page">
    <div class="fi-dashboard-top mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                Welcome, {{ filament()->auth()->user()?->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Platform overview — filter by municipality or view all.
            </p>
        </div>

        @if (method_exists($this, 'filtersForm'))
            <div class="fi-dashboard-filters w-full shrink-0 sm:max-w-xs">
                {{ $this->filtersForm }}
            </div>
        @endif
    </div>

    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetData(),
            ]
        "
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
