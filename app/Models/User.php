<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasProfilePhoto;

    protected $fillable = [
        'name',
        'email',
        'password',
        'beneficiary_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Beneficiary profile
    public function beneficiary()
    {
        return $this->hasOne(\App\Models\Beneficiary::class);
    }

    // Employer profile
    public function employer()
    {
        return $this->hasOne(\App\Models\Employer::class);
    }
}
