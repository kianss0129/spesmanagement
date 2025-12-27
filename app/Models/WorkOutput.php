<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOutput extends Model
{
    use HasFactory;

    protected $fillable = ['employer_id','beneficiary_id','file_path','original_name'];
}
