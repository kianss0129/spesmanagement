<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Exam extends Model
{
    protected $fillable = [
        'application_id',
        'exam_date',
        'location',
        'notes',
        'status',
        'result'
    ];
    protected $casts = [
    'exam_date' => 'datetime',
];


   public function application()
{
    return $this->belongsTo(Application::class, 'application_id');
}
}

