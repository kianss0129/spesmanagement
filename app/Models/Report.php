<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employer;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['employer_id','title','body','file_path'];

     public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
