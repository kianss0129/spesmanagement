<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficiary;
use App\Models\Employer;

class Attendance extends Model
{
    protected $guarded = [];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }
}