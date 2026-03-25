<?php

namespace App\Models;
// Add this import at the top
use Laravel\Fortify\TwoFactorAuthenticatable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable =[
            'name', 'email', 'phone_number', 'password', 'role',
            'municipality_id', 'id_card_path', 'id_card_back_path', 'id_number', 'dob', 'identity_verified_at',
            'place_of_birth', 'father_name', 'provider_name',
            'provider_id', 'provider_token', 'two_factor_secret',
            'two_factor_recovery_codes', 'two_factor_confirmed_at', 'is_active',
        ];
    /**
     
     *
     * @var list<string>
     */
    protected $hidden =[
        'password', 
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return[
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function citizenIdentityProfile(): HasOne
    {
        return $this->hasOne(CitizenIdentityProfile::class);
    }
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'citizen_id');
    }
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'citizen_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'citizen_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function canAccessPanel(Panel $panel): bool
{
    return $this->role === 'admin' && $this->is_active;
}
}
