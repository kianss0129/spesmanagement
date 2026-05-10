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
        'status',
        'result', // ✅ important
    ];


    protected $casts = [
        'scheduled_at' => 'datetime',
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

