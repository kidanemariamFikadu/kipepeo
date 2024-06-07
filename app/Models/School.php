<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Get all of the students for the School
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, SchoolStudent::class, 'school_id', 'id','id','student_id');
    }

    public function scopeSearch($query, $value)
    {
        $query->where('name', 'like', "%{$value}%"); //->orWhere('email','like',"%{$value}%");
    }
}
