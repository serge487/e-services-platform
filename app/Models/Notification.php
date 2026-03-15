<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'id', 'type', 'notifiable_type',
        'notifiable_id', 'data', 'read_at'
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}