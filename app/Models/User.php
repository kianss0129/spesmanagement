<?php

namespace App\Models;
use App\Models\Rating;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Jetstream\HasProfilePhoto; // ✅ Add this

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasProfilePhoto;

    // app/Models/User.php
    protected $fillable = [
    'name',
    'email',
    'password',
    'beneficiary_type',
];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed'];

    // Each user may have a beneficiary profile
   public function beneficiary()
{
    return $this->hasOne(\App\Models\Beneficiary::class);
}

}
