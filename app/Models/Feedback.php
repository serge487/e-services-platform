<?php

namespace App\Models;

use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'service_request_id',
        'office_id', 'service_id', 'citizen_id',
        'rating', 'citizen_comment', 'office_response',
        'office_response_is_private',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'office_response_is_private' => 'boolean',
            'rating' => 'integer',
        ];
    }

    /** Citizen review visible on the public service page and map averages. */
    public function scopePublicReview($query)
    {
        return $query->where('is_private', false);
    }

    public function hasPublicOfficeResponse(): bool
    {
        return filled($this->office_response) && ! $this->office_response_is_private;
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }
}
