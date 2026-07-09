<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolunteerActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'volunteer_attendance_id',
        'volunteer_id',
        'activity_type_id',
        'date',
        'notes',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(VolunteerAttendance::class, 'volunteer_attendance_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'volunteer_activity_student')->withTimestamps();
    }
}
