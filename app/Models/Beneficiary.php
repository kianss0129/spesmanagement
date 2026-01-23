<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\PesoOffice;

class Beneficiary extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id', 'first_name', 'last_name', 'email', 'phone', 'school_id', 'peso_office_id', 'documents'
];


    protected $casts = [
        'documents' => 'array',
    ];

    /**
     * Get the applications for the beneficiary.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get the attendances for the beneficiary.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the employer ratings for the beneficiary.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(\App\Models\EmployerRating::class);
    }

    /**
     * Get the school that the beneficiary belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Get the PESO office that the beneficiary belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pesoOffice()
{
    return $this->belongsTo(PesoOffice::class, 'peso_id');
}

    /**
     * Get the work history for the beneficiary.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workHistory()
    {
        return $this->hasMany(\App\Models\WorkOutput::class, 'beneficiary_id');
    }
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

}
