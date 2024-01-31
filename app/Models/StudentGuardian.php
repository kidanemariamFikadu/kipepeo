<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'guardian_name',
        'guardian_phone',
        'student_id',
        'is_primary',
    ];
}
