<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Office;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'citizen_id' => User::factory(),
            'office_id' => Office::factory(),
            'service_id' => Service::factory(),
            'service_request_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'citizen_comment' => fake()->realText(fake()->numberBetween(60, 240)),
            'is_private' => false,
            'office_response' => null,
            'office_response_is_private' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Feedback $feedback): void {
            if ($feedback->service_id && ! $feedback->office_id) {
                $service = Service::find($feedback->service_id);
                if ($service) {
                    $feedback->office_id = $service->office_id;
                }
            }
        })->afterCreating(function (Feedback $feedback): void {
            if ($feedback->service_request_id) {
                return;
            }

            $serviceRequest = ServiceRequest::factory()->create([
                'citizen_id' => $feedback->citizen_id,
                'service_id' => $feedback->service_id,
                'status' => 'Approved',
            ]);

            Payment::query()->create([
                'service_request_id' => $serviceRequest->id,
                'amount' => $serviceRequest->service?->price ?? fake()->randomFloat(2, 25, 150),
                'currency' => 'USD',
                'payment_method' => 'cash',
                'status' => 'paid',
                'paid_at' => fake()->dateTimeBetween('-3 months', 'now'),
            ]);

            $feedback->update(['service_request_id' => $serviceRequest->id]);
        });
    }

    public function forOffice(Office $office): static
    {
        $service = $office->services()->inRandomOrder()->first()
            ?? Service::factory()->create(['office_id' => $office->id]);

        return $this->state(fn () => [
            'office_id' => $office->id,
            'service_id' => $service->id,
        ]);
    }

    public function publicReview(): static
    {
        return $this->state(fn () => ['is_private' => false]);
    }

    public function privateReview(): static
    {
        return $this->state(fn () => ['is_private' => true]);
    }

    public function withPublicMunicipalityReply(): static
    {
        return $this->state(fn () => [
            'office_response' => fake()->realText(120),
            'office_response_is_private' => false,
        ]);
    }

    public function withPrivateMunicipalityReply(): static
    {
        return $this->state(fn () => [
            'office_response' => fake()->realText(120),
            'office_response_is_private' => true,
        ]);
    }
}
