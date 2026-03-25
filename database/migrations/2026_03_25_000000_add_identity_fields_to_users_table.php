<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('identity_verified_at')->nullable()->after('dob');
            $table->string('place_of_birth')->nullable()->after('identity_verified_at');
            $table->string('father_name')->nullable()->after('place_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['identity_verified_at', 'place_of_birth', 'father_name']);
        });
    }
};
