<?php

namespace App\Filament\Support;

use App\Models\Office;

class AdminDashboardFilters
{
    /**
     * @param  array<string, mixed>|null  $filters
     * @return list<int>|null  null = all offices (no municipality filter)
     */
    public static function officeIds(?array $filters): ?array
    {
        $municipalityId = $filters['municipality_id'] ?? null;

        if ($municipalityId === null || $municipalityId === '') {
            return null;
        }

        return Office::query()
            ->where('municipality_id', (int) $municipalityId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
