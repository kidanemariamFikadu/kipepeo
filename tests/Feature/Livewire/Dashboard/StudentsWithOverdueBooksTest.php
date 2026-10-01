<?php

use App\Livewire\Dashboard\StudentsWithOverdueBooks;
use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function overdueTestBook(string $title): Book
{
    return Book::create([
        'title' => $title,
        'author' => 'Author',
        'publisher' => 'Pub',
        'class' => 'A',
        'category' => 'Fiction',
        'copies' => 1,
    ]);
}

test('a student holding a book past its due date is listed', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Late Reader', 'dob' => '2012-01-01', 'gender' => 'male']);
    $book = overdueTestBook('An Overdue Title');

    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(20), 'due_at' => now()->subDays(5),
    ]);

    $component = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class);
    $row = $component->viewData('students')->first();

    expect($row['studentName'])->toBe('Late Reader');
    expect($row['count'])->toBe(1);
    expect($row['daysOverdue'])->toBe(5);
    expect($row['books']->pluck('title')->all())->toContain('An Overdue Title');
    expect($component->html())->toContain(route('student-detail', $student->id));
});

test('each overdue book offers a Return button wired to its own rental', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Late Reader', 'dob' => '2012-01-01', 'gender' => 'male']);

    $first = Rental::create([
        'book_id' => overdueTestBook('First Title')->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(20), 'due_at' => now()->subDays(5),
    ]);
    $second = Rental::create([
        'book_id' => overdueTestBook('Second Title')->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(20), 'due_at' => now()->subDays(9),
    ]);

    $html = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class)->html();

    // Returning is a per-book action, so each rental gets its own button.
    expect($html)->toContain("rentalId: {$first->id}");
    expect($html)->toContain("rentalId: {$second->id}");
    // It opens the shared confirmation modal rather than returning directly.
    expect($html)->toContain('book.return-book');
});

test('returning a book drops it from the card', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Late Reader', 'dob' => '2012-01-01', 'gender' => 'male']);

    $rental = Rental::create([
        'book_id' => overdueTestBook('An Overdue Title')->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(20), 'due_at' => now()->subDays(5),
    ]);

    $component = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class);
    expect($component->viewData('students'))->toHaveCount(1);

    $rental->update(['returned_at' => now()]);

    // The card refreshes on the event ReturnBook dispatches.
    $component->dispatch('rental-changed', ['type' => 'success', 'content' => 'Book returned successfully']);

    expect($component->viewData('students'))->toHaveCount(0);
    expect($component->viewData('totalBooks'))->toBe(0);
});

test('books still within their due date and returned books are excluded', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Fine Reader', 'dob' => '2012-01-01', 'gender' => 'male']);
    $book = overdueTestBook('A Fine Title');

    // Not yet due.
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDay(), 'due_at' => now()->addWeek(),
    ]);
    // Was overdue, but has been returned.
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(30), 'due_at' => now()->subDays(10), 'returned_at' => now()->subDay(),
    ]);

    $component = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class);

    expect($component->viewData('students'))->toHaveCount(0);
    expect($component->viewData('totalBooks'))->toBe(0);
    expect($component->html())->toContain('Nothing overdue');
});

test('a student with several overdue books is one row showing the longest overdue', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Serial Borrower', 'dob' => '2012-01-01', 'gender' => 'female']);

    foreach ([3, 12, 7] as $daysLate) {
        Rental::create([
            'book_id' => overdueTestBook("Title {$daysLate}")->id,
            'student_id' => $student->id,
            'user_id' => $user->id,
            'rented_at' => now()->subDays(30),
            'due_at' => now()->subDays($daysLate),
        ]);
    }

    $component = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class);
    $rows = $component->viewData('students');

    expect($rows)->toHaveCount(1);
    expect($rows->first()['count'])->toBe(3);
    expect($rows->first()['daysOverdue'])->toBe(12);
    expect($component->viewData('totalBooks'))->toBe(3);
});

test('students are ordered by how long they have been overdue', function () {
    $user = User::factory()->create();

    foreach (['Mild' => 2, 'Worst' => 40, 'Middling' => 15] as $name => $daysLate) {
        $student = Student::create(['name' => $name, 'dob' => '2012-01-01', 'gender' => 'male']);
        Rental::create([
            'book_id' => overdueTestBook("Book for {$name}")->id,
            'student_id' => $student->id,
            'user_id' => $user->id,
            'rented_at' => now()->subDays(60),
            'due_at' => now()->subDays($daysLate),
        ]);
    }

    $names = Livewire::actingAs($user)
        ->test(StudentsWithOverdueBooks::class)
        ->viewData('students')
        ->pluck('studentName')
        ->all();

    expect($names)->toBe(['Worst', 'Middling', 'Mild']);
});

test('a soft-deleted student does not appear as a blank row', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Removed Reader', 'dob' => '2012-01-01', 'gender' => 'male']);

    Rental::create([
        'book_id' => overdueTestBook('Orphaned Title')->id,
        'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(20), 'due_at' => now()->subDays(5),
    ]);

    $student->delete();

    $component = Livewire::actingAs($user)->test(StudentsWithOverdueBooks::class);

    expect($component->viewData('students'))->toHaveCount(0);
});
