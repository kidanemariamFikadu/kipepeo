<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'class',
        'category',
        'copies',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    function bookCopies()
    {
        return $this->hasMany(BookCopy::class);
    }

    public function getAvailableCopiesAttribute()
    {
        return $this->bookCopies()->where('status', 'available')->count();
    }

    public function getLostCopies()
    {
        return $this->bookCopies()->where('status', 'lost')->count();
    }

    public function getStolenCopies()
    {
        return $this->bookCopies()->where('status', 'stolen')->count();
    }

    public function scopeSearch($query, $value)
    {
        $query->where('title', 'like', "%{$value}%")->orWhere('author', 'like', "%{$value}%");
    }
}
