<?php

namespace App\Models;

use App\Enums\ActivityCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
    ];

    protected $casts = [
        'category' => ActivityCategory::class,
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(VolunteerActivity::class);
    }
}
