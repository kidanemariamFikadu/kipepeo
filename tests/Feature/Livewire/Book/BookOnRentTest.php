<?php

use App\Livewire\Book\BookOnRent;
use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function makeRentalWithStatus(string $status): Rental
{
    $book = Book::create(['title' => 'Book ' . uniqid(), 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Student ' . uniqid(), 'dob' => '2010-01-01', 'gender' => 'male']);

    return match ($status) {
        'returned' => Rental::create(['book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id, 'rented_at' => now()->subDays(5), 'due_at' => now()->addDays(2), 'returned_at' => now()]),
        'overdue' => Rental::create(['book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id, 'rented_at' => now()->subDays(10), 'due_at' => now()->subDays(2)]),
        default => Rental::create(['book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $user->id, 'rented_at' => now(), 'due_at' => now()->addDays(5)]),
    };
}

test('status filter narrows the rentals shown', function () {
    $user = User::factory()->create();
    makeRentalWithStatus('returned');
    makeRentalWithStatus('overdue');
    makeRentalWithStatus('borrowed');

    $component = Livewire::actingAs($user)->test(BookOnRent::class);
    expect($component->viewData('booksOnRent'))->toHaveCount(3);

    $component->set('status', 'returned');
    expect($component->viewData('booksOnRent'))->toHaveCount(1);

    $component->set('status', 'overdue');
    expect($component->viewData('booksOnRent'))->toHaveCount(1);

    $component->set('status', 'borrowed');
    expect($component->viewData('booksOnRent'))->toHaveCount(2);
});

test('pagination moves to the next page without a full-page reload', function () {
    $user = User::factory()->create();
    for ($i = 0; $i < 15; $i++) {
        makeRentalWithStatus('borrowed');
    }

    $component = Livewire::actingAs($user)->test(BookOnRent::class, ['perPage' => 10]);
    expect($component->viewData('booksOnRent')->currentPage())->toBe(1);

    $component->call('nextPage');
    expect($component->viewData('booksOnRent')->currentPage())->toBe(2);
});
