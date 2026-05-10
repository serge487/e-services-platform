<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipality_events', function (Blueprint $table) {
            $table->id();

            // Which office is broadcasting this event
            $table->foreignId('office_id')
                ->constrained('offices')
                ->cascadeOnDelete();

            // Which municipality staff member created it
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description');
            $table->string('place');
            $table->dateTime('event_date');
            $table->string('occasion')->nullable(); // e.g. "National Day", "Office Closure"

            // How many citizens were notified (denormalized for display)
            $table->unsignedInteger('notified_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipality_events');
    }
};