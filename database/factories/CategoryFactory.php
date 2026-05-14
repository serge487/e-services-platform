<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
