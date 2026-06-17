<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $guarded = [];

    protected $casts = [
        'employer_acknowledged_at' => 'datetime',
    ];

    public function employerAcknowledgedBy()
    {
        return $this->belongsTo(User::class, 'employer_acknowledged_by');
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function interview()
    {
        return $this->hasOne(Interview::class);
    }

    public function employerRating()
    {
        return $this->hasOne(EmployerRating::class);
    }

    public function batch()
    {
        return $this->belongsTo(\App\Models\Batch::class);
    }
}
