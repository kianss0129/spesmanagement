<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $guarded = [];

    protected $dates = ['scheduled_at'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_listing_id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }
}
