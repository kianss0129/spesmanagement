<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'beneficiary_id',
        'application_id',
        'job_listing_id',
        'work_date',
        'title',
        'description',
        'accomplishments',
        'hours_worked',
        'status',
        'submitted_by',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
        'file_path',
        'original_name',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours_worked' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
