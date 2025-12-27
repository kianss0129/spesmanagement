<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $guarded = [];

    protected $dates = ['scheduled_at'];

    public function application()
    {
        // Older code assumed an application_id column. The interviews table uses `job_id` and `beneficiary_id`.
        // Keep this method removed to avoid queries against a non-existent `application_id` column.
        return null;
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function employer()
    {
        return $this->belongsTo(\App\Models\Employer::class);
    }
}
