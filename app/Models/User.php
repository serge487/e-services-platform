<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable =[
            'name', 'email', 'phone_number', 'password', 'role', 
            'municipality_id', 'id_card_path', 'id_number', 'dob','provider_name', 
            'provider_id', 'provider_token', 'two_factor_secret', 
            'two_factor_recovery_codes', 'two_factor_confirmed_at', 'is_active'
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
            'is_active' => 'boolean',
        ];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
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
}
