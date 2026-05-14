<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficerTimeSlot>
 */
class OfficerTimeSlotFactory extends Factory
{
    protected $model = OfficerTimeSlot::class;

    public function definition(): array
    {
        $hour = fake()->numberBetween(8, 15);
        $start = sprintf('%02d:00:00', $hour);
        $end = sprintf('%02d:00:00', $hour + 1);

        return [
            'office_id' => Office::factory(),
            'officer_id' => User::factory(),
            'slot_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'is_booked' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (OfficerTimeSlot $slot): void {
            $office = $slot->office;
            if (! $office) {
                return;
            }

            $staff = User::query()
                ->where('role', 'office_staff')
                ->where('office_id', $office->id)
                ->first()
                ?? User::factory()->officeStaff()->create([
                    'municipality_id' => $office->municipality_id,
                ]);

            if ((int) $slot->officer_id !== (int) $staff->id) {
                $slot->forceFill(['officer_id' => $staff->id])->save();
            }
        });
    }
}
