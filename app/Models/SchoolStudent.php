<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
