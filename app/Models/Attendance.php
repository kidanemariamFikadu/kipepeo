<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'current_in',
        'total_time',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attrs()
    {
        return $this->hasMany(AttendanceAttr::class);
    }

    
}
