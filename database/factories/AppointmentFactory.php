<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\OfficerTimeSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'service_request_id' => null,
            'officer_time_slot_id' => OfficerTimeSlot::factory(),
            'citizen_id' => User::factory(),
            'status' => fake()->randomElement([
                'scheduled', 'confirmed', 'completed', 'cancelled', 'no_show',
            ]),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Appointment $appointment): void {
            $request = $appointment->serviceRequest;
            if ($request && (int) $appointment->citizen_id !== (int) $request->citizen_id) {
                $appointment->forceFill(['citizen_id' => $request->citizen_id])->save();
            }
        });
    }
}
