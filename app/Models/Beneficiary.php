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
        'onboarding_completed_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'approved' => 'boolean',
        'onboarding_completed_at' => 'datetime',
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
        return $this->hasMany(\App\Models\EmployerRating::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function pesoOffice()
    {
        return $this->belongsTo(PesoOffice::class);
    }

    public function workHistory()
    {
        return $this->hasMany(\App\Models\WorkOutput::class, 'beneficiary_id');
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
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'approved' => false,
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
}
