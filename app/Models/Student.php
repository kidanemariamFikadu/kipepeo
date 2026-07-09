<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'dob',
        'gender',
        'graduated_at',
        'graduated_grade_id',
    ];

    protected $casts = [
        'graduated_at' => 'datetime',
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

    /**
     * Get the grade the student graduated from, if any.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function graduatedGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'graduated_grade_id');
    }

    /**
     * Get all of the volunteer activities (tutoring, mentorship, etc.) this student has received.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function volunteerActivities(): BelongsToMany
    {
        return $this->belongsToMany(VolunteerActivity::class, 'volunteer_activity_student')->withTimestamps();
    }

    public function getCurrentSchoolAttribute()
    {
        return $this->schools()->where('is_current', true)->first()?->school;
    }

    public function getCurrentGradeAttribute()
    {
        return $this->grades()->where('is_current', true)->first()?->gradeTable;
    }

    public function getCurrentAttendanceAttribute()
    {
        return $this->attendances()->where('current_in', true)
            ->whereDate('date', now())->first()?->current_in;
    }

    public function getTodayTotalTimeAttribute()
    {
        $total= $this->attendances()->whereDate('date', now())->first()?->total_time;
        return $this->secondsToHms($total);
    }

    function secondsToHms($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }


    public function getStudentAgeAttribute(){
        return  Carbon::parse($this->dob)->age;
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%"); //->orWhere('email','like',"%{$value}%");
    }

    /**
     * Students who haven't graduated -- the ones who should show up in the
     * active roster, attendance search, etc.
     */
    public function scopeActive($query)
    {
        $query->whereNull('graduated_at');
    }

    /**
     * Mark this student as having graduated from the given grade: drops
     * their current grade and school membership and stamps the graduation
     * date, same outcome whether triggered individually or via a bulk
     * promotion run.
     */
    public function graduate(int $gradeId): void
    {
        GradeStudent::where('student_id', $this->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        SchoolStudent::where('student_id', $this->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $this->update([
            'graduated_at' => now(),
            'graduated_grade_id' => $gradeId,
        ]);
    }
}
