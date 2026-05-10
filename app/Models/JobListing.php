<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'location',
        'type',
        'slots',
        'closing_date',
        'assigned_beneficiary_id',
        'salary',
    ];

    public function employer()
    {
       return $this->belongsTo(\App\Models\Employer::class, 'employer_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'job_listing_id');
    }
    
}
