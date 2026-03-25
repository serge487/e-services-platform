<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'citizen',
            'phone_number' => fake()->unique()->numerify('+9617#######'),
            'identity_verified_at' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Citizen with ID verification completed (2FA may still be required on login). */
    public function identityVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'citizen',
            'identity_verified_at' => now(),
        ]);
    }
}
