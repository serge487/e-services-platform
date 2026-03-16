<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@admin.com')],
            [
                'name'      => env('ADMIN_NAME', 'Admin'),
                'email'     => env('ADMIN_EMAIL', 'admin@admin.com'),
                'password'  => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}