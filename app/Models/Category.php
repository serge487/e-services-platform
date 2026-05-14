<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['office_id', 'name'];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
