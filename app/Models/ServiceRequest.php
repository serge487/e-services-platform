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
        'qr_code_token', 'office_notes', 'accepted_by', 'accepted_at'
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function citizenDisplayStatus(): string
    {
        return match ($this->status) {
            'Pending', 'In Review' => 'Under Review',
            default => $this->status,
        };
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

    public function hasPendingPayment(): bool
{
    return $this->payment !== null && $this->payment->isPending();
}

public function isPaid(): bool
{
    return $this->payment !== null && $this->payment->isPaid();
}

public function needsPayment(): bool
{
    // Payment panel shows only after officer marks as taken (In Review)
    return $this->status === 'In Review' && $this->payment !== null;
}
}
