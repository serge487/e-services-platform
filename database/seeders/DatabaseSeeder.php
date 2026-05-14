<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LebaneseMunicipalityServicesSeeder::class,
            LebanonWideMunicipalitiesSeeder::class,
            CitizenAppointmentsDemoSeeder::class,
            MunicipalityServicesAndAppointmentsSeeder::class,
        ]);
    }
}
