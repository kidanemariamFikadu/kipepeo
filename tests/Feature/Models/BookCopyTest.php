<?php

use App\Models\Book;
use App\Models\BookCopy;

test('creating a book copy auto-generates a zero-padded copy number from its id', function () {
    $book = Book::create([
        'title' => 'Test Book',
        'author' => 'Author',
        'publisher' => 'Pub',
        'class' => 'A',
        'category' => 'Fiction',
        'copies' => 1,
    ]);

    $copy = BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    expect($copy->fresh()->copy_number)->toBe(str_pad($copy->id, 4, '0', STR_PAD_LEFT));
});

test('markAsLost and markAsStolen update the status', function () {
    $book = Book::create([
        'title' => 'Test Book',
        'author' => 'Author',
        'publisher' => 'Pub',
        'class' => 'A',
        'category' => 'Fiction',
        'copies' => 1,
    ]);
    $copy = BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    $copy->markAsLost();
    expect($copy->fresh()->status)->toBe('lost');

    $copy->markAsStolen();
    expect($copy->fresh()->status)->toBe('stolen');
});

test('book relationship resolves the owning book', function () {
    $book = Book::create([
        'title' => 'Test Book',
        'author' => 'Author',
        'publisher' => 'Pub',
        'class' => 'A',
        'category' => 'Fiction',
        'copies' => 1,
    ]);
    $copy = BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    expect($copy->book->id)->toBe($book->id);
});
