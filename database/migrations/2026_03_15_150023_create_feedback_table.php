<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices');
            $table->foreignId('service_id')->nullable()->constrained('services');
            $table->foreignId('citizen_id')->constrained('users');
            $table->tinyInteger('rating');
            $table->text('citizen_comment');
            $table->text('office_response')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};