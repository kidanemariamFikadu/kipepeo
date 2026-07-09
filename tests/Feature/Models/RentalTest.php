<?php

use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;

function makeRental(array $overrides = []): Rental
{
    $book = Book::create(['title' => 'Book Title', 'author' => 'Book Author', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $user = User::factory()->create(['role' => 'admin', 'name' => 'Librarian Jane']);
    $student = Student::create(['name' => 'Student Name', 'dob' => '2010-01-01', 'gender' => 'male']);

    return Rental::create(array_merge([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now(),
        'due_at' => now()->addDays(7),
    ], $overrides));
}

test('search scope matches by book title, book author, checked-out-by name, or checked-out-to name', function () {
    $rental = makeRental();

    expect(Rental::search('Book Title')->count())->toBe(1);
    expect(Rental::search('Book Author')->count())->toBe(1);
    expect(Rental::search('Librarian Jane')->count())->toBe(1);
    expect(Rental::search('Student Name')->count())->toBe(1);
    expect(Rental::search('Nonexistent')->count())->toBe(0);
});

test('relationships resolve book, checkedOutBy, and checkedOutTo', function () {
    $rental = makeRental();

    expect($rental->book)->not->toBeNull();
    expect($rental->checkedOutBy)->not->toBeNull();
    expect($rental->checkedOutTo)->not->toBeNull();
});
