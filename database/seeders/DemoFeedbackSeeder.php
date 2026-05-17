<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Office;
use Illuminate\Database\Seeder;

class DemoFeedbackSeeder extends Seeder
{
    /**
     * Seed sample public reviews for offices that already have services.
     *
     * Run: php artisan db:seed --class=DemoFeedbackSeeder
     */
    public function run(): void
    {
        $offices = Office::query()
            ->whereHas('services')
            ->with('services')
            ->get();

        if ($offices->isEmpty()) {
            $this->command?->warn('No offices with services found. Run municipality seeders first.');

            return;
        }

        foreach ($offices as $office) {
            $count = fake()->numberBetween(4, 14);

            Feedback::factory()
                ->count($count)
                ->forOffice($office)
                ->publicReview()
                ->create();

            Feedback::factory()
                ->count(fake()->numberBetween(1, 3))
                ->forOffice($office)
                ->privateReview()
                ->create();

            Feedback::factory()
                ->count(fake()->numberBetween(2, 5))
                ->forOffice($office)
                ->publicReview()
                ->withPublicMunicipalityReply()
                ->create();
        }

        $this->command?->info('Demo feedback seeded for '.$offices->count().' office(s).');
    }
}
