<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['grade', 'next_grade_id'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function gradeStudents()
    {
        return $this->hasMany(GradeStudent::class, 'grade');
    }

    public function nextGrade()
    {
        return $this->belongsTo(Grade::class, 'next_grade_id');
    }
}
