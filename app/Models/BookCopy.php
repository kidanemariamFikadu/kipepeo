<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'status','copy_number'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function markAsLost()
    {
        $this->status = 'lost';
        $this->save();
    }

    public function markAsStolen()
    {
        $this->status = 'stolen';
        $this->save();
    }

    protected static function booted()
    {
        static::created(function ($bookCopy) {
            $bookCopy->copy_number = str_pad($bookCopy->id, 4, '0', STR_PAD_LEFT);
            $bookCopy->save();
        });
    }
}
