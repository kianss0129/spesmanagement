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
        'output_quality',
        'attitude',
        'work_attitude',
        'communication',
        'overall',
        'comment',
    ];

    /**
     * Hide DB column names from JSON, expose aliased names instead.
     */
    protected $hidden = [
        'output_quality',
        'work_attitude',
    ];

    /**
     * Append virtual accessors to JSON output.
     */
    protected $appends = [
        'work_quality',
        'attitude',
    ];

    /**
     * Map 'work_quality' to actual DB column 'output_quality'.
     * This allows all controllers/views to use 'work_quality' seamlessly.
     */
    public function setWorkQualityAttribute($value)
    {
        $this->attributes['output_quality'] = $value;
    }

    public function getWorkQualityAttribute()
    {
        return $this->attributes['output_quality'] ?? null;
    }

    /**
     * Map 'attitude' to actual DB column 'work_attitude'.
     * This allows all controllers/views to use 'attitude' seamlessly.
     */
    public function setAttitudeAttribute($value)
    {
        $this->attributes['work_attitude'] = $value;
    }

    public function getAttitudeAttribute()
    {
        return $this->attributes['work_attitude'] ?? null;
    }

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