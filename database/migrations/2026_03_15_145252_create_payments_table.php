<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests');
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->decimal('exchange_rate', 15, 8)->nullable();
            $table->enum('payment_method', ['card', 'crypto']);
            $table->string('transaction_reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};