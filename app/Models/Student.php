<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dob',
        'gender'
    ];

    /**
     * Get all of the shools for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schools(): HasMany
    {
        return $this->hasMany(SchoolStudent::class, 'student_id');
    }

    /**
     * Get all of the guardians for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class, 'student_id');
    }

    /**
     * Get all of the attendances for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id','id');
    }

    /**
     * Get all of the grades for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grades(): HasMany
    {
        return $this->hasMany(GradeStudent::class, 'student_id');
    }

    public function getCurrentSchoolAttribute()
    {
        return $this->schools()->where('is_current', true)->first()?->school;
    }

    public function getCurrentGradeAttribute()
    {
        return $this->grades()->where('is_current', true)->first()->gradeTable;
    }

    public function getCurrentAttendanceAttribute()
    {
        return $this->attendances()->where('current_in', true)
            ->whereDate('date', now())->first()?->current_in;
    }

    public function getTodayTotalTimeAttribute()
    {
        $total= $this->attendances()->whereDate('date', now())->first()?->total_time;
        return Carbon::createFromTimestamp($total)->format('H:i:s');
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%"); //->orWhere('email','like',"%{$value}%");
    }
}
