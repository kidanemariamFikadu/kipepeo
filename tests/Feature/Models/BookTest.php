<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;

function makeBook(array $overrides = []): Book
{
    return Book::create(array_merge([
        'title' => 'Test Book',
        'author' => 'Test Author',
        'publisher' => 'Test Publisher',
        'class' => 'A',
        'category' => 'Fiction',
        'copies' => 3,
    ], $overrides));
}

test('available copies attribute counts only copies with available status', function () {
    $book = makeBook();
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'lost']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'stolen']);

    expect($book->available_copies)->toBe(2);
});

test('getLostCopies and getStolenCopies count matching statuses', function () {
    $book = makeBook();
    BookCopy::create(['book_id' => $book->id, 'status' => 'lost']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'lost']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'stolen']);

    expect($book->getLostCopies())->toBe(2);
    expect($book->getStolenCopies())->toBe(1);
});

test('search scope matches title or author', function () {
    makeBook(['title' => 'Zebra Adventures', 'author' => 'Someone Else']);
    makeBook(['title' => 'Unrelated', 'author' => 'Zebra Smith']);
    makeBook(['title' => 'Nothing Matching', 'author' => 'Nobody']);

    $results = Book::search('Zebra')->get();

    expect($results)->toHaveCount(2);
});

test('rentals relationship returns rentals for the book', function () {
    $book = makeBook();
    $user = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Renter', 'dob' => '2010-01-01', 'gender' => 'male']);

    Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now(),
        'due_at' => now()->addDays(7),
    ]);

    expect($book->rentals)->toHaveCount(1);
    expect($book->rentals->first()->student_id)->toBe($student->id);
});

test('available copies subtracts books that are currently out on loan', function () {
    // Regression test: renting never marks a book_copy as rented, so
    // counting copies with status "available" reported the shelf total and
    // the availability guard in Rent::rent() was a no-op -- a single copy
    // could be lent to unlimited students.
    $book = makeBook();
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    $user = User::factory()->create();
    $borrower = Student::create(['name' => 'Borrower', 'dob' => '2010-01-01', 'gender' => 'male']);

    expect($book->available_copies)->toBe(2);

    Rental::create(['book_id' => $book->id, 'student_id' => $borrower->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addWeek()]);
    expect($book->fresh()->available_copies)->toBe(1);

    $returned = Rental::create(['book_id' => $book->id, 'student_id' => $borrower->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addWeek()]);
    expect($book->fresh()->available_copies)->toBe(0);

    // Returning a copy puts it back into circulation.
    $returned->update(['returned_at' => now()]);
    expect($book->fresh()->available_copies)->toBe(1);
});

test('available copies never reports a negative number', function () {
    $book = makeBook();
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    $user = User::factory()->create();
    $borrower = Student::create(['name' => 'Borrower', 'dob' => '2010-01-01', 'gender' => 'male']);

    // Existing data already contains books lent beyond their shelf count.
    foreach (range(1, 3) as $i) {
        Rental::create(['book_id' => $book->id, 'student_id' => $borrower->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addWeek()]);
    }

    expect($book->fresh()->available_copies)->toBe(0);
});
