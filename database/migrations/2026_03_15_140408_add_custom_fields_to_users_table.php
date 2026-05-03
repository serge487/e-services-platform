<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone_number')->unique()->nullable()->after('email');
        $table->enum('role', ['admin', 'municipality', 'citizen'])->default('citizen')->after('phone_number');
        $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->nullOnDelete()->after('role');
        $table->string('id_card_path')->nullable()->after('municipality_id');
        $table->text('two_factor_secret')->nullable()->after('id_card_path');
        $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
        $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        $table->boolean('is_active')->default(true)->after('two_factor_confirmed_at');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['municipality_id']);
        $table->dropColumn([
            'phone_number', 'role', 'municipality_id', 'id_card_path',
            'two_factor_secret', 'two_factor_recovery_codes',
            'two_factor_confirmed_at', 'is_active'
        ]);
    });
}
};
