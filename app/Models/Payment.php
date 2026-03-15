<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'service_request_id', 'amount', 'currency',
        'exchange_rate', 'payment_method',
        'transaction_reference', 'status'
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'exchange_rate' => 'decimal:8',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}