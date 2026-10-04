<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'is_active' => 'boolean',
        ];
    }

    // A user belongs to one role (department)
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // A Sales user registers many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // A Route user uploads many evidence photos
    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class);
    }

    // A user makes many status changes
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }
}
