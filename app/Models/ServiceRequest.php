<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    protected $fillable = [
        'citizen_id', 'service_id', 'status',
        'qr_code_token', 'office_notes'
    ];

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function requestDocuments(): HasMany
    {
        return $this->hasMany(RequestDocument::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}