<?php

namespace Database\Factories;

use App\Models\Municipality;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Municipality>
 */
class MunicipalityFactory extends Factory
{
    protected $model = Municipality::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Municipality',
        ];
    }
}
