<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'student_id', 'user_id', 'rented_at', 'returned_at', 'due_at'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checkedOutTo()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function scopeSearch($query, $value)
    {
        return $query->whereHas('book', function ($q) use ($value) {
            $q->where('title', 'like', "%{$value}%");
        })->orWhereHas('checkedOutBy', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
        })->orWhereHas('checkedOutTo', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
        })->orWhereHas('book', function ($q) use ($value) {
            $q->where('author', 'like', "%{$value}%");
        });
    }
}
