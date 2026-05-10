<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: change payment_method enum to support all 3 methods
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method 
            ENUM('card','crypto','whish','cash') NOT NULL");

        // Step 2: change status enum to add 'paid'
        DB::statement("ALTER TABLE payments MODIFY COLUMN status 
            ENUM('pending','completed','failed','paid') NOT NULL DEFAULT 'pending'");

        Schema::table('payments', function (Blueprint $table) {
            // When citizen paid (completed)
            $table->timestamp('paid_at')->nullable()->after('status');

            // Whish-specific
            $table->string('whish_phone')->nullable()->after('paid_at');
            $table->string('whish_reference')->nullable()->after('whish_phone');

            // Crypto-specific
            $table->string('crypto_coin')->nullable()->after('whish_reference');
            $table->string('crypto_wallet_address')->nullable()->after('crypto_coin');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'paid_at',
                'whish_phone',
                'whish_reference',
                'crypto_coin',
                'crypto_wallet_address',
            ]);
        });

        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method 
            ENUM('card','crypto') NOT NULL");

        DB::statement("ALTER TABLE payments MODIFY COLUMN status 
            ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending'");
    }
};