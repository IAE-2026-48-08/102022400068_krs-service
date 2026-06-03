<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KrsItem extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
