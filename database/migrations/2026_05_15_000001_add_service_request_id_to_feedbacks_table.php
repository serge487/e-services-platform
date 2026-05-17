<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->foreignId('service_request_id')
                ->nullable()
                ->after('id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->unique('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropUnique(['service_request_id']);
            $table->dropConstrainedForeignId('service_request_id');
        });
    }
};
