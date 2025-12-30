<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded = [];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
