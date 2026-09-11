<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'class',
        'category',
        'category_id',
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

    public function bookCategory()
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    /**
     * Kept as a string accessor/mutator (backed by the book_categories table
     * via category_id) so existing call sites and imports can keep reading
     * and writing a plain category name instead of juggling an id.
     */
    public function getCategoryAttribute()
    {
        return $this->bookCategory?->name;
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['category_id'] = blank($value)
            ? null
            : BookCategory::firstOrCreate(['name' => $value])->id;
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
