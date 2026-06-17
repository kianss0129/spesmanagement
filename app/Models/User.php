<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Return a URL for the user's profile photo, falling back to a default
     * image when the configured storage file does not exist. This prevents
     * broken image 404s if the DB points to a missing file.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            $disk = config('jetstream.profile_photo_disk', 'public');
            if (Storage::disk($disk)->exists($this->profile_photo_path)) {
                return Storage::disk($disk)->url($this->profile_photo_path);
            }
        }

        return '/images/default-avatar.png';
    }
}
