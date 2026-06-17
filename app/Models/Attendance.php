<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficiary;
use App\Models\Employer;

class Attendance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }

    public function application()
    {
        return $this->belongsTo(\App\Models\Application::class, 'application_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
