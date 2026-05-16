<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RevenueService
{
    /**
     * Revenue = sum of payment amounts for service requests marked Completed.
     */
    public static function countedInRevenueQuery(?array $officeIds = null): Builder
    {
        $query = Payment::query()->whereHas(
            'serviceRequest',
            fn (Builder $sr) => $sr->where('status', ServiceRequest::STATUS_COMPLETED)
        );

        if ($officeIds !== null) {
            $query->whereHas(
                'serviceRequest.service',
                fn (Builder $s) => $s->whereIn('office_id', $officeIds)
            );
        }

        return $query;
    }

    public static function totalAmount(?array $officeIds = null): float
    {
        return (float) self::countedInRevenueQuery($officeIds)->sum('amount');
    }

    public static function formattedTotal(?array $officeIds = null): string
    {
        return '$'.number_format(self::totalAmount($officeIds), 2);
    }

    /**
     * Monthly revenue (oldest → newest), keyed by when the request was completed.
     *
     * @return array<int, float>
     */
    public static function monthlyTotals(int $monthsBack = 5, ?array $officeIds = null): array
    {
        $months = collect(range($monthsBack, 0))->map(fn (int $i) => Carbon::now()->subMonths($i));

        return $months->map(function (Carbon $month) use ($officeIds): float {
            return (float) self::countedInRevenueQuery($officeIds)
                ->whereHas(
                    'serviceRequest',
                    fn (Builder $sr) => $sr
                        ->whereYear('updated_at', $month->year)
                        ->whereMonth('updated_at', $month->month)
                )
                ->sum('amount');
        })->all();
    }
}
