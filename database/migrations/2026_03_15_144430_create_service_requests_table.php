<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('users');
            $table->foreignId('service_id')->constrained('services');
            $table->enum('status', [
                'Pending', 'In Review', 'Missing Documents',
                'Approved', 'Rejected', 'Completed'
            ])->default('Pending');
            $table->string('qr_code_token')->unique();
            $table->text('office_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};