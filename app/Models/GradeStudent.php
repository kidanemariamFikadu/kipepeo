<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'grade',
        'is_current',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function gradeTable()
    {
        return $this->belongsTo(Grade::class, 'grade');
    }
}
