<?php

namespace Database\Factories;

use App\Models\Municipality;
use App\Models\Office;
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

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'municipality_id' => null,
            'office_id' => null,
            'is_active' => true,
        ]);
    }

    public function municipalityAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'municipality',
            'municipality_id' => Municipality::factory(),
            'office_id' => null,
        ]);
    }

    /**
     * Ensures an office exists for the user’s municipality and sets office_id.
     */
    public function officeStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'office_staff',
            'municipality_id' => Municipality::factory(),
        ])->afterCreating(function (User $user): void {
            if ($user->role !== 'office_staff' || $user->office_id) {
                return;
            }

            $municipalityId = $user->municipality_id;
            $office = Office::query()->where('municipality_id', $municipalityId)->first()
                ?? Office::factory()->create(['municipality_id' => $municipalityId]);

            $user->forceFill(['office_id' => $office->id])->save();
        });
    }
}
