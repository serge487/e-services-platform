<?php

namespace App\Models;

use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use HasFactory;

    protected $fillable = [
        'municipality_id', 'name', 'address',
        'latitude', 'longitude', 'working_hours',
        'contact_info',
    ];

    // Replace with this:
    protected $casts = [
        'working_hours' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function officerTimeSlots(): HasMany
    {
        return $this->hasMany(OfficerTimeSlot::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
