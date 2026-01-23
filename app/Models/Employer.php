<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // make sure this is imported

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'email', 'status', 
        'contact_person', 'phone', 'address'
    ];

    // Employer belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Job listings
    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }

    // Ratings
    public function ratings()
    {
        return $this->hasMany(EmployerRating::class);
    }

    // Status helpers
    public function activate() { $this->status = 'active'; $this->save(); }
    public function deactivate() { $this->status = 'inactive'; $this->save(); }
    public function suspend() { $this->status = 'suspended'; $this->save(); }

    public function isActive() { return $this->status === 'active'; }
    public function isInactive() { return $this->status === 'inactive'; }
    public function isSuspended() { return $this->status === 'suspended'; }
}
