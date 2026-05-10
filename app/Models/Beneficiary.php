<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'school_id',
        'peso_office_id',
        'documents',
        'status',
        'approved',
        'approval_status',
        'approved_at',
        'rejection_reason',
        'resubmit_at',
        'onboarding_completed_at',
        'skills',
        'parent_name',
        'birthdate',
        'gender',
        'address',
        'program',
        'year_level',
        'employer_id',
        'job_id',
        'employment_status',
    ];

    protected $casts = [
        'documents' => 'array',
        'approved' => 'boolean',
        'onboarding_completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'resubmit_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function ratings()
    {
        return $this->hasMany(\App\Models\EmployerRating::class, 'beneficiary_id');
    }

    public function evaluations()
    {
        return $this->hasMany(\App\Models\EmployerRating::class, 'beneficiary_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function pesoOffice()
    {
        return $this->belongsTo(PesoOffice::class);
    }

    public function employer()
    {
        return $this->belongsTo(\App\Models\Employer::class);
    }

    public function workHistory()
    {
        return $this->hasMany(\App\Models\WorkOutput::class, 'beneficiary_id');
    }

    public function workSchedules()
    {
        return $this->hasMany(\App\Models\WorkSchedule::class, 'beneficiary_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved' => true,
            'approved_at' => now(),
        ]);
    }

    public function reject($reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved' => false,
            'rejection_reason' => $reason,
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => 'pending',
            'approved' => false,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->user && $this->user->profile_photo_path
            ? asset('storage/' . $this->user->profile_photo_path)
            : asset('images/default-profile.png');
    }
}