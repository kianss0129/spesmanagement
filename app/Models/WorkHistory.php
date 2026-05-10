<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkHistory extends Model
{
    protected $table = 'work_histories';

    protected $fillable = [
        'beneficiary_id',
        'employer_id',
        'rating',
        'comment',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}