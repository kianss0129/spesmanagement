<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = ['company_name', 'email', 'status', 'contact_person', 'phone', 'address'];

    public function jobListings() { return $this->hasMany(JobListing::class); }
    public function ratings() { return $this->hasMany(EmployerRating::class); }

    // -----------------
    // Status Helpers
    // -----------------
    public function activate() { $this->status = 'active'; $this->save(); }
    public function deactivate() { $this->status = 'inactive'; $this->save(); }
    public function suspend() { $this->status = 'suspended'; $this->save(); }

    public function isActive() { return $this->status === 'active'; }
    public function isInactive() { return $this->status === 'inactive'; }
    public function isSuspended() { return $this->status === 'suspended'; }
}
