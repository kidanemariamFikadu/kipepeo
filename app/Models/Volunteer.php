<?php

namespace App\Models;

use App\Enums\VolunteerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Volunteer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
        'status',
        'hourly_rate',
    ];

    protected $casts = [
        'status' => VolunteerStatus::class,
        'hourly_rate' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(VolunteerAttendance::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VolunteerActivity::class);
    }

    public function isActive(): bool
    {
        return $this->status === VolunteerStatus::Active;
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%");
    }

    /**
     * Volunteers who can currently be checked in -- deactivated volunteers
     * stay visible in Settings/history but drop out of the check-in roster.
     */
    public function scopeActive($query)
    {
        $query->where('status', VolunteerStatus::Active);
    }

    public function secondsToHms($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}
