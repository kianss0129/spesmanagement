<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Interview extends Model
{
    protected $fillable = [
        'application_id',
        'job_listing_id',
        'employer_id',
        'beneficiary_id',
        'scheduled_at',
        'meet_link',
        'schedule_group_id',
        'batch_title',
        'scheduled_by',
        'interviewer_id',
        'end_at',
        'original_schedule_at',
        'rescheduled_at',
        'reschedule_reason',
        'instructions',
        'notify_beneficiaries',
        'status',
        'remarks',
        'evaluated_at',
        'result', // ✅ important
    ];


    protected $casts = [
        'scheduled_at' => 'datetime',
        'end_at' => 'datetime',
        'original_schedule_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'evaluated_at' => 'datetime',
        'notify_beneficiaries' => 'boolean',
    ];


    // ✅ DEFAULT VALUES
    protected $attributes = [
        'status' => 'scheduled',
        'result' => 'pending',
    ];


    // ✅ CONSTANTS
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';


    const RESULT_PENDING = 'pending';
    const RESULT_PASSED = 'passed';
    const RESULT_FAILED = 'failed';
    const RESULT_NEEDS_REVIEW = 'needs_review';


    /**
     * RELATIONS
     */


    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }


    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class, 'job_listing_id');
    }


    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }


    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }


    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }


    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }


    /**
     * HELPERS
     */


    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }


    public function isPassed()
    {
        return $this->result === self::RESULT_PASSED;
    }


    public function isFailed()
    {
        return $this->result === self::RESULT_FAILED;
    }
}
