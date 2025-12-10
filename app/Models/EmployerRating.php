<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerRating extends Model
{
    protected $guarded = [];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function employer()
    {
        return $this->belongsTo(\App\Models\Employer::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Models\Application::class);
    }
}
