<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Category;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\OfficerTimeSlot;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Ensures each municipality office has up to 20 services and 20 appointments (fake / demo data).
 * Idempotent: only fills up to the target counts. Safe to re-run without wiping tables.
 */
class MunicipalityServicesAndAppointmentsSeeder extends Seeder
{
    private const TARGET_SERVICES = 20;

    private const TARGET_APPOINTMENTS = 20;

    public function run(): void
    {
        $citizens = User::query()
            ->where('role', 'citizen')
            ->inRandomOrder()
            ->limit(200)
            ->get();

        if ($citizens->count() < 30) {
            $toCreate = max(0, 50 - $citizens->count());
            if ($toCreate > 0) {
                User::factory()->count($toCreate)->create();
            }
            $citizens = User::query()
                ->where('role', 'citizen')
                ->inRandomOrder()
                ->limit(200)
                ->get();
        }

        Municipality::query()
            ->with('offices')
            ->orderBy('name')
            ->each(function (Municipality $municipality) use ($citizens): void {
                $office = $municipality->offices->first();
                if (! $office instanceof Office) {
                    return;
                }

                $this->seedServicesUpToTarget($office);
                $this->seedAppointmentsUpToTarget($office, $citizens);
            });
    }

    private function seedServicesUpToTarget(Office $office): void
    {
        $current = Service::query()->where('office_id', $office->id)->count();
        $missing = max(0, self::TARGET_SERVICES - $current);
        if ($missing === 0) {
            return;
        }

        $category = Category::query()->firstOrCreate(
            [
                'office_id' => $office->id,
                'name' => 'Demo catalogue (auto)',
            ],
        );

        for ($i = 0; $i < $missing; $i++) {
            Service::factory()->create([
                'category_id' => $category->id,
            ]);
        }
    }

    private function seedAppointmentsUpToTarget(Office $office, Collection $citizens): void
    {
        $current = Appointment::query()
            ->whereHas('officerTimeSlot', function ($q) use ($office): void {
                $q->where('office_id', $office->id);
            })
            ->count();

        $missing = max(0, self::TARGET_APPOINTMENTS - $current);
        if ($missing === 0) {
            return;
        }

        $officer = User::query()
            ->where('role', 'office_staff')
            ->where('office_id', $office->id)
            ->first()
            ?? User::factory()->officeStaff()->create([
                'municipality_id' => $office->municipality_id,
            ]);

        $baseDay = Carbon::now()->addMonths(4)->addDays($office->id % 40);

        for ($i = 0; $i < $missing; $i++) {
            $slotDate = $baseDay->copy()->addDays($i)->format('Y-m-d');

            $slot = OfficerTimeSlot::query()->create([
                'office_id' => $office->id,
                'officer_id' => $officer->id,
                'slot_date' => $slotDate,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_booked' => false,
            ]);

            Appointment::query()->create([
                'service_request_id' => null,
                'officer_time_slot_id' => $slot->id,
                'citizen_id' => $citizens->random()->id,
                'status' => fake()->randomElement(['scheduled', 'confirmed', 'completed']),
            ]);

            $slot->update(['is_booked' => true]);
        }
    }
}
