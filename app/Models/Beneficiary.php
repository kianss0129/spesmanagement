<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'birthdate', 'gender', 'address', 'contact_number', 'email'];

    protected $guarded = [];

    protected $casts = [
        'documents' => 'array',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function ratings()
{
    return $this->hasMany(\App\Models\EmployerRating::class);
}

    
}
