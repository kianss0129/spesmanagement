<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'application_id',
        'contract_date',
        'location',
        'notes',
        'status',
        'result'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
