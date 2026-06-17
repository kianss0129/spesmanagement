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
        'schedule_group_id',
        'batch_title',
        'scheduled_by',
        'end_at',
        'original_schedule_at',
        'rescheduled_at',
        'reschedule_reason',
        'instructions',
        'notify_beneficiaries',
        'status',
        'result'
    ];

    protected $casts = [
        'contract_date' => 'datetime',
        'end_at' => 'datetime',
        'original_schedule_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'notify_beneficiaries' => 'boolean',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
