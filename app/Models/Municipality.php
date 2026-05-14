<?php

namespace App\Models;

use Database\Factories\MunicipalityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    /** @use HasFactory<MunicipalityFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
