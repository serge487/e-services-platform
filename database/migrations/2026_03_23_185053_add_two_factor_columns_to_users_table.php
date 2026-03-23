<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columns already exist in users table from original migration — skip
    }

    public function down(): void
    {
        // Nothing to reverse
    }
};