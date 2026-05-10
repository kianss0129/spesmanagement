<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerRating extends Model
{
    protected $table = 'employer_ratings';

    /**
     * Mass assignable fields (SAFE)
     */
    protected $fillable = [
        'application_id',
        'beneficiary_id',
        'employer_id',
        'punctuality',
        'work_quality',
        'attitude',
        'communication',
        'overall',
        'comment',
    ];

    /**
     * Beneficiary being rated
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * Employer who gave the rating
     * (IMPORTANT: using User model, not Employer model)
     */
  public function employer()
{
    return $this->belongsTo(Employer::class, 'employer_id', 'id');
}

    /**
     * Average rating (computed attribute)
     */
    public function getAverageAttribute()
    {
        return (
            (float) $this->punctuality +
            (float) $this->work_quality +
            (float) $this->attitude +
            (float) $this->communication +
            (float) $this->overall
        ) / 5;
    }
}