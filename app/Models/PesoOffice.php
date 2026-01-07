<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesoOffice extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'contact_number'];

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class, 'peso_id');
    }
}
