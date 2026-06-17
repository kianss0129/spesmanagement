<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'skill_category_id',
    ];

    /**
     * Get the category of this skill
     */
    public function skillCategory()
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * Get the job listings that have this skill
     */
    public function jobListings()
    {
        return $this->belongsToMany(JobListing::class, 'job_listing_skills')->withTimestamps();
    }
    public function beneficiaries()
    {
        return $this->belongsToMany(
            Beneficiary::class,
            'beneficiary_skill',
            'skill_id',
            'beneficiary_id'
        )->withTimestamps();
    }
}
