<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'day',
        'start_time',
        'end_time',
        // add your other columns here
    ];
}