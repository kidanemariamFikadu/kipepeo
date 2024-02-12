<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SchoolStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'is_current'
    ];

    /**
     * Get the school that owns the SchoolStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }


    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }


    /**
     * Get all of the attendances for the SchoolStudent
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, Attendance::class, 'student_id', 'id', 'student_id', 'student_id');
    }
}
