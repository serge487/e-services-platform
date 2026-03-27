<?php

namespace App\Models;

// Add this import at the top
use Database\Factories\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'phone_number', 'password', 'role',
        'municipality_id', 'office_id', 'id_card_path', 'id_card_back_path', 'id_number', 'dob', 'identity_verified_at',
        'place_of_birth', 'father_name', 'provider_name',
        'provider_id', 'provider_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'two_factor_confirmed_at', 'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
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
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->role === 'office_staff' && $user->municipality_id) {
                $user->office_id = Office::query()
                    ->where('municipality_id', $user->municipality_id)
                    ->value('id');
            }

            if ($user->role !== 'office_staff') {
                $user->office_id = null;
            }
        });
    }

    public function isMunicipalityAdmin(): bool
    {
        return $this->role === 'municipality';
    }

    public function isOfficeStaff(): bool
    {
        return $this->role === 'office_staff';
    }

    public function canAccessMunicipalityPortal(): bool
    {
        return $this->isMunicipalityAdmin() || $this->isOfficeStaff();
    }

    /**
     * @return list<int>
     */
    public function accessibleOfficeIds(): array
    {
        if ($this->role === 'municipality') {
            return $this->municipality?->offices()->pluck('id')->all() ?? [];
        }

        if ($this->role === 'office_staff' && $this->office_id) {
            return [(int) $this->office_id];
        }

        return [];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
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
