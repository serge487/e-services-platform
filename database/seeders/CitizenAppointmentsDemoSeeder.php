<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Normalizes obviously swapped office map coordinates (Lebanon bbox) and
 * adds future officer slots plus at least one upcoming appointment per citizen when missing.
 *
 * Safe to run multiple times (idempotent via firstOrCreate / existence checks).
 */
class CitizenAppointmentsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->normalizeOfficeMapCoordinates();

        $offices = $this->officesForDemoSlots();
        foreach ($offices as $office) {
            $officer = User::query()
                ->where('role', 'office_staff')
                ->where('office_id', $office->id)
                ->first()
                ?? User::factory()->officeStaff()->create([
                    'municipality_id' => $office->municipality_id,
                ]);

            $this->seedFutureSlotsForOffice($office, $officer);
        }

        $this->ensureCitizensHaveUpcomingAppointment();
    }

    /**
     * If latitude/longitude look swapped (common when copying coords), fix them in place.
     */
    private function normalizeOfficeMapCoordinates(): void
    {
        // Typical mainland Lebanon: ~33.1–34.7 N, ~35.05–36.7 E
        Office::query()->eachById(function (Office $office): void {
            $lat = (float) $office->latitude;
            $lng = (float) $office->longitude;

            $latPlausible = $lat >= 33.0 && $lat <= 34.85;
            $lngPlausible = $lng >= 35.0 && $lng <= 36.75;

            if ($latPlausible && $lngPlausible) {
                return;
            }

            $swappedLat = $lng;
            $swappedLng = $lat;
            if ($swappedLat >= 33.0 && $swappedLat <= 34.85 && $swappedLng >= 35.0 && $swappedLng <= 36.75) {
                $office->update([
                    'latitude' => round($swappedLat, 8),
                    'longitude' => round($swappedLng, 8),
                ]);
            }
        }, 100);
    }

    /**
     * @return list<Office>
     */
    private function officesForDemoSlots(): array
    {
        $picked = [];

        $baabda = Office::query()
            ->where(function ($q): void {
                $q->where('name', 'like', '%Baabda%')
                    ->orWhereHas('municipality', function ($m): void {
                        $m->where('name', 'like', '%Baabda%');
                    });
            })
            ->first();

        $beirut = Office::query()
            ->where(function ($q): void {
                $q->where('name', 'like', '%Beirut%')
                    ->orWhereHas('municipality', function ($m): void {
                        $m->where('name', 'like', '%Beirut%');
                    });
            })
            ->first();

        $tripoli = Office::query()
            ->where(function ($q): void {
                $q->where('name', 'like', '%Tripoli%')
                    ->orWhereHas('municipality', function ($m): void {
                        $m->where('name', 'like', '%Tripoli%');
                    });
            })
            ->first();

        foreach ([$baabda, $beirut, $tripoli] as $office) {
            if ($office) {
                $picked[$office->id] = $office;
            }
        }

        if ($picked === []) {
            $fallback = Office::query()->orderBy('id')->first();
            if ($fallback) {
                $picked[$fallback->id] = $fallback;
            }
        }

        return array_values($picked);
    }

    private function seedFutureSlotsForOffice(Office $office, User $officer): void
    {
        $blocks = [
            ['10:00:00', '11:00:00'],
            ['14:00:00', '15:30:00'],
        ];

        for ($day = 1; $day <= 10; $day++) {
            $date = Carbon::now()->addDays($day)->toDateString();

            foreach ($blocks as [$start, $end]) {
                OfficerTimeSlot::query()->firstOrCreate(
                    [
                        'office_id' => $office->id,
                        'officer_id' => $officer->id,
                        'slot_date' => $date,
                        'start_time' => $start,
                    ],
                    [
                        'end_time' => $end,
                        'is_booked' => false,
                    ]
                );
            }
        }
    }

    private function citizenHasUpcomingAppointment(int $citizenId): bool
    {
        return Appointment::query()
            ->where('citizen_id', $citizenId)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereHas('officerTimeSlot', function ($q): void {
                $q->where(function ($inner): void {
                    $inner->whereDate('slot_date', '>', Carbon::now()->toDateString())
                        ->orWhere(function ($today): void {
                            $today->whereDate('slot_date', Carbon::now()->toDateString())
                                ->where('start_time', '>', Carbon::now()->format('H:i:s'));
                        });
                });
            })
            ->exists();
    }

    private function ensureCitizensHaveUpcomingAppointment(): void
    {
        User::query()
            ->where('role', 'citizen')
            ->orderBy('id')
            ->each(function (User $citizen): void {
                if ($this->citizenHasUpcomingAppointment($citizen->id)) {
                    return;
                }

                $slot = OfficerTimeSlot::query()
                    ->with('office')
                    ->where('is_booked', false)
                    ->where(function ($query): void {
                        $query->whereDate('slot_date', '>', Carbon::now()->toDateString())
                            ->orWhere(function ($todayQuery): void {
                                $todayQuery->whereDate('slot_date', Carbon::now()->toDateString())
                                    ->where('start_time', '>', Carbon::now()->format('H:i:s'));
                            });
                    })
                    ->orderBy('slot_date')
                    ->orderBy('start_time')
                    ->first();

                if (! $slot) {
                    return;
                }

                DB::transaction(function () use ($slot, $citizen): void {
                    $locked = OfficerTimeSlot::query()
                        ->lockForUpdate()
                        ->find($slot->id);

                    if (! $locked || $locked->is_booked) {
                        return;
                    }

                    Appointment::query()->create([
                        'service_request_id' => null,
                        'officer_time_slot_id' => $locked->id,
                        'citizen_id' => $citizen->id,
                        'status' => 'scheduled',
                    ]);

                    $locked->update(['is_booked' => true]);
                });
            });
    }
}
