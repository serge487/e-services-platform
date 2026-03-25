<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizen_identity_profiles', function (Blueprint $table) {
            $table->string('id_document_front_path')->nullable()->after('user_id');
            $table->string('id_document_back_path')->nullable()->after('id_document_front_path');

            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('grandfather_name')->nullable()->after('mother_name');
            $table->string('gender')->nullable()->after('grandfather_name');
            $table->string('blood_type')->nullable()->after('gender');
            $table->string('registry_number')->nullable()->after('blood_type');
            $table->date('id_issue_date')->nullable()->after('registry_number');
            $table->date('id_expiry_date')->nullable()->after('id_issue_date');

            $table->text('ocr_raw_text_front')->nullable()->after('ocr_raw_text');
            $table->text('ocr_raw_text_back')->nullable()->after('ocr_raw_text_front');
        });

        if (Schema::hasColumn('citizen_identity_profiles', 'id_document_path')) {
            DB::table('citizen_identity_profiles')
                ->whereNotNull('id_document_path')
                ->whereNull('id_document_front_path')
                ->update([
                    'id_document_front_path' => DB::raw('id_document_path'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('citizen_identity_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'id_document_front_path',
                'id_document_back_path',
                'mother_name',
                'grandfather_name',
                'gender',
                'blood_type',
                'registry_number',
                'id_issue_date',
                'id_expiry_date',
                'ocr_raw_text_front',
                'ocr_raw_text_back',
            ]);
        });
    }
};
