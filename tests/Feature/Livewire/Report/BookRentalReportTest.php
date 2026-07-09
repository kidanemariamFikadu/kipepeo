<?php

use App\Livewire\Report\BookRentalReport;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function makeRentalTestBook(string $title, string $category = 'Fiction'): Book
{
    return Book::create(['title' => $title, 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => $category, 'copies' => 1]);
}

test('mounting the component auto-loads data for the default (this month) range', function () {
    $user = User::factory()->create();
    $book = makeRentalTestBook('Book A');
    $student = Student::create(['name' => 'Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    Rental::create(['book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addDays(7)]);

    $component = Livewire::actingAs($user)->test(BookRentalReport::class);

    expect($component->get('totalRentals'))->toBe(1);
});

test('filter separates rentals returned on time from those returned late', function () {
    $user = User::factory()->create();
    $book = makeRentalTestBook('Book A');
    $student = Student::create(['name' => 'Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(10), 'due_at' => now()->subDays(3), 'returned_at' => now()->subDays(4),
    ]);
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subDays(10), 'due_at' => now()->subDays(3), 'returned_at' => now()->subDays(1),
    ]);

    $component = Livewire::actingAs($user)->test(BookRentalReport::class)
        ->set('fromDate', now()->subDays(20)->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    expect($component->get('totalRentals'))->toBe(2);
    expect($component->get('returnedOnTime'))->toBe(1);
    expect($component->get('returnedLate'))->toBe(1);
});

test('currently borrowed and overdue counts are live and ignore the selected date range', function () {
    // Regression-style guard: these must reflect real-time state, not the analytics
    // date range, since a librarian checking "what's overdue right now" shouldn't
    // have to first pick the correct date filter to see it.
    $user = User::factory()->create();
    $book = makeRentalTestBook('Book A');
    $student = Student::create(['name' => 'Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id,
        'rented_at' => now()->subMonths(3), 'due_at' => now()->subMonths(2),
    ]);

    $component = Livewire::actingAs($user)->test(BookRentalReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    expect($component->get('totalRentals'))->toBe(0);
    expect($component->viewData('currentlyBorrowed'))->toBe(1);
    expect($component->viewData('currentlyOverdue'))->toBe(1);
});

test('an empty date range does not crash the average days to return calculation', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(BookRentalReport::class)
        ->set('fromDate', now()->subYears(5)->format('Y-m-d'))
        ->set('toDate', now()->subYears(5)->format('Y-m-d'))
        ->call('filter');

    expect($component->get('totalRentals'))->toBe(0);
    expect($component->get('avgDaysToReturn'))->toBe(0.0);
});

test('filter requires the to-date to be on or after the from-date', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BookRentalReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->subDay()->format('Y-m-d'))
        ->call('filter')
        ->assertHasErrors(['toDate']);
});

test('the full rental list used for printing includes every rental in range, not just the current page', function () {
    $user = User::factory()->create();
    $book = makeRentalTestBook('Book A');
    $student = Student::create(['name' => 'Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    for ($i = 0; $i < 3; $i++) {
        Rental::create(['book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addDays(7)]);
    }

    $component = Livewire::actingAs($user)->test(BookRentalReport::class)->set('perPage', 2);

    expect($component->viewData('rentals'))->toHaveCount(2);
    expect($component->viewData('fullRentals'))->toHaveCount(3);
});

test('inventory totals count book copies by status', function () {
    $user = User::factory()->create();
    $book = makeRentalTestBook('Book A');
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'lost']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'stolen']);

    $component = Livewire::actingAs($user)->test(BookRentalReport::class);

    expect($component->viewData('inventoryTotals'))->toBe(['available' => 1, 'lost' => 1, 'stolen' => 1]);
});
