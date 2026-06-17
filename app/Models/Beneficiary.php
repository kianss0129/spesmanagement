<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',

        // Basic Info
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'phone',
        'contact_number',

        // IDs / Relations
        'school_id',
        'peso_office_id',
        'employer_id',
        'job_id',

        // Documents / Status
        'documents',
        'status',
        'approved',
        'approval_status',
        'approved_at',
        'rejection_reason',
        'resubmit_at',
        'submitted_at',
        'onboarding_completed_at',
        'completed_at',
        'draft_status',

        // Personal
        'birthdate',
        'birth_date',
        'age',
        'gender',
        'sex',
        'civil_status',
        'place_of_birth',
        'citizenship',
        'facebook_account',

        // Address
        'address',
        'present_address',
        'barangay',
        'city',
        'province',

        // Parent / Family
        'father_name',
        'father_contact',
        'father_occupation',

        'mother_name',
        'mother_contact',
        'mother_occupation',

        'parent_name',
        'parent_guardian_name',
        'relationship',

        'family_income',

        // Category
        'category',

        // School / Education
        'school_name',
        'school_address',
        'education_level',
        'school_year',
        'year_level',
        'program',
        'course',

        'last_school_attended',
        'highest_attainment',
        'year_last_attended',

        // Employment / SPES
        'employment_status',
        'former_employer',
        'displacement_reason',
        'displacement_date',

        'previous_spes',
        'spes_count',

        // Onboarding Progress
        'onboarding_step',
        'completion_percentage',
        'completed_steps',
    ];

    protected $casts = [
        'documents' => 'array',

        'approved' => 'boolean',
        'previous_spes' => 'boolean',

        'completed_steps' => 'array',

        'approved_at' => 'datetime',
        'resubmit_at' => 'datetime',
        'submitted_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'completed_at' => 'datetime',
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
        return $this->hasMany(
            EmployerRating::class,
            'beneficiary_id'
        );
    }

    public function evaluations()
    {
        return $this->hasMany(
            EmployerRating::class,
            'beneficiary_id'
        );
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
        return $this->belongsTo(
            Employer::class
        );
    }

    public function job()
    {
        return $this->belongsTo(
            JobListing::class,
            'job_id'
        );
    }

    public function workHistory()
    {
        return $this->hasMany(
            WorkOutput::class,
            'beneficiary_id'
        );
    }

    public function workSchedules()
    {
        return $this->hasMany(
            WorkSchedule::class,
            'beneficiary_id'
        );
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'beneficiary_skill', 'beneficiary_id', 'skill_id')
            ->withTimestamps();
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
        return trim(
            "{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}"
        );
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->user &&
            $this->user->profile_photo_path
            ? asset(
                'storage/' .
                $this->user->profile_photo_path
            )
            : asset(
                'images/default-profile.png'
            );
    }
}
