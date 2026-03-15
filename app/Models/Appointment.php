<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'service_request_id', 'officer_time_slot_id',
        'citizen_id', 'status'
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function officerTimeSlot(): BelongsTo
    {
        return $this->belongsTo(OfficerTimeSlot::class);
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }
}