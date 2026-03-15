<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfficerTimeSlot extends Model
{
    protected $fillable = [
        'office_id', 'officer_id', 'slot_date',
        'start_time', 'end_time', 'is_booked'
    ];

    protected function casts(): array
    {
        return [
            'slot_date'  => 'date',
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
            'is_booked'  => 'boolean',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }
}