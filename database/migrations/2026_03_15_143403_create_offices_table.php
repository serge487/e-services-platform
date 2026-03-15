<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained('municipalities')->cascadeOnDelete();
            $table->string('name');
            $table->text('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('working_hours');
            $table->string('contact_info');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};