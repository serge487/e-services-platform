<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds office_staff role, links office users to a single office, and enforces
     * one office row per municipality (one government office per municipal entity).
     *
     * MySQL: extends the users.role ENUM. SQLite (tests): role is already a string column.
     * If migrate fails on offices_municipality_id_unique, dedupe offices per municipality first.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('office_id')
                ->nullable()
                ->after('municipality_id')
                ->constrained('offices')
                ->nullOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY COLUMN role ENUM('admin','municipality','office_staff','citizen') NOT NULL DEFAULT 'citizen'"
            );
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->unique('municipality_id');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropUnique(['municipality_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY COLUMN role ENUM('admin','municipality','citizen') NOT NULL DEFAULT 'citizen'"
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('office_id');
        });
    }
};
