<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerAttendanceAttr extends Model
{
    use HasFactory;

    protected $fillable = [
        'volunteer_attendance_id',
        'volunteer_id',
        'date',
        'time_in',
        'time_out',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(VolunteerAttendance::class, 'volunteer_attendance_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }
}
