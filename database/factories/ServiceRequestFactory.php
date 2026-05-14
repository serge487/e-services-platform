<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'citizen_id' => User::factory(),
            'service_id' => Service::factory(),
            'status' => fake()->randomElement([
                'Pending', 'In Review', 'Missing Documents',
                'Approved', 'Rejected', 'Completed',
            ]),
            'qr_code_token' => (string) Str::uuid(),
            'office_notes' => fake()->optional(0.4)->paragraph(),
            'accepted_by' => null,
            'accepted_at' => null,
        ];
    }

    public function accepted(User $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_by' => $officer->id,
            'accepted_at' => now(),
            'status' => fake()->randomElement(['In Review', 'Approved', 'Completed']),
        ]);
    }
}
