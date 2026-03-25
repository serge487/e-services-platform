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
        'id_document_path',
        'ocr_raw_text',
        'extracted_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
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
