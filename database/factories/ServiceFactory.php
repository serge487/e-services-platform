<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 1, 500),
            'duration_days' => fake()->numberBetween(1, 30),
            'required_documents' => [
                fake()->sentence(),
                fake()->sentence(),
            ],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Service $service): void {
            if ($service->category_id && $service->office_id) {
                $category = Category::query()->find($service->category_id);
                if ($category && (int) $service->office_id !== (int) $category->office_id) {
                    $service->office_id = $category->office_id;
                }
            }

            if ($service->category_id && ! $service->office_id) {
                $category = Category::query()->find($service->category_id);
                if ($category) {
                    $service->office_id = $category->office_id;
                }

                return;
            }

            if ($service->office_id && ! $service->category_id) {
                $category = Category::factory()->create(['office_id' => $service->office_id]);
                $service->category_id = $category->id;

                return;
            }

            if (! $service->office_id && ! $service->category_id) {
                $office = Office::factory()->create();
                $category = Category::factory()->create(['office_id' => $office->id]);
                $service->office_id = $office->id;
                $service->category_id = $category->id;
            }
        });
    }
}
