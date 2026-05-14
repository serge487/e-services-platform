<?php

namespace Database\Factories;

use App\Models\Municipality;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    protected $model = Office::class;

    public function definition(): array
    {
        return [
            'municipality_id' => Municipality::factory(),
            'name' => fake()->company().' Office',
            'address' => fake()->streetAddress().', '.fake()->city(),
            'latitude' => fake()->latitude(33.0, 34.8),
            'longitude' => fake()->longitude(35.0, 36.8),
            'working_hours' => [
                'monday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
                'tuesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
                'wednesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
                'thursday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
                'friday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '12:30'],
                'saturday' => ['is_open' => false, 'open_time' => null, 'close_time' => null],
                'sunday' => ['is_open' => false, 'open_time' => null, 'close_time' => null],
            ],
            'contact_info' => fake()->phoneNumber(),
        ];
    }
}
