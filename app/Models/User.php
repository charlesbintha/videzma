<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'locale',
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
        ];
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }

    public function serviceRequestsAsClient()
    {
        return $this->hasMany(ServiceRequest::class, 'client_id');
    }

    public function serviceRequestsAsDriver()
    {
        return $this->hasMany(ServiceRequest::class, 'driver_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'driver_id');
    }

    public function appNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(Location::class)->latestOfMany('captured_at');
    }

    public function navigationSessions()
    {
        return $this->hasMany(NavigationSession::class, 'driver_id');
    }

    public function auditActions()
    {
        return $this->hasMany(AuditAction::class, 'actor_id');
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
