<?php

namespace App\Filament\Pages;

use App\Models\Municipality;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $view = 'filament.pages.dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('municipality_id')
                    ->label('Municipality')
                    ->placeholder('All Municipalities')
                    ->options(
                        Municipality::query()->orderBy('name')->pluck('name', 'id')->all()
                    )
                    ->nullable()
                    ->native(false)
                    ->searchable()
                    ->live(),
            ]);
    }
}
