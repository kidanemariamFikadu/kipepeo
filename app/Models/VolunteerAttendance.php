<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VolunteerAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'volunteer_id',
        'date',
        'current_in',
        'total_time',
    ];

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function attrs(): HasMany
    {
        return $this->hasMany(VolunteerAttendanceAttr::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VolunteerActivity::class);
    }
}
