<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'service_request_id',
        'amount',
        'currency',
        'exchange_rate',
        'payment_method',
        'transaction_reference',
        'status',
        'paid_at',
        'whish_phone',
        'whish_reference',
        'crypto_coin',
        'crypto_wallet_address',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:8',
            'paid_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return in_array($this->status, ['completed', 'paid'], true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'whish' => 'Whish Money',
            'crypto' => 'Cryptocurrency',
            'cash' => 'Cash on Pickup',
            'card' => 'Card',
            default => ucfirst($this->payment_method),
        };
    }

    public function methodIcon(): string
    {
        return match ($this->payment_method) {
            'whish' => 'bi-phone',
            'crypto' => 'bi-currency-bitcoin',
            'cash' => 'bi-cash-coin',
            'card' => 'bi-credit-card',
            default => 'bi-cash',
        };
    }
}
