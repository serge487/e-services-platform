<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitizenIdentityProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'national_id_number',
        'date_of_birth',
        'place_of_birth',
        'father_name',
        'mother_name',
        'grandfather_name',
        'gender',
        'blood_type',
        'registry_number',
        'id_issue_date',
        'id_expiry_date',
        'id_document_path',
        'id_document_front_path',
        'id_document_back_path',
        'ocr_raw_text',
        'ocr_raw_text_front',
        'ocr_raw_text_back',
        'extracted_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'id_issue_date' => 'date',
            'id_expiry_date' => 'date',
            'extracted_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
