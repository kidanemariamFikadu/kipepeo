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

    /**
     * Renting does not currently reserve a specific book_copy row, so a
     * copy's status stays "available" while it is out on loan. Open rentals
     * therefore have to be subtracted here -- without it this returns the
     * shelf count rather than what is actually available, and the check in
     * Rent::rent() lets a single copy be lent to unlimited students.
     */
    public function getAvailableCopiesAttribute()
    {
        $onShelf = $this->bookCopies()->where('status', 'available')->count();
        $onLoan = $this->rentals()->whereNull('returned_at')->count();

        return max(0, $onShelf - $onLoan);
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
