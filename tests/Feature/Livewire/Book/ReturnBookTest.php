<?php

use App\Livewire\Book\ReturnBook;
use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('returnBook marks the rental as returned with a comment', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $student = Student::create(['name' => 'Borrower', 'dob' => '2010-01-01', 'gender' => 'male']);
    $rental = Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now()->subDays(2),
        'due_at' => now()->addDays(5),
    ]);

    Livewire::actingAs($user)
        ->test(ReturnBook::class, ['rentalId' => $rental->id])
        ->set('comment', 'Returned in good condition')
        ->call('returnBook')
        ->assertDispatched('rental-changed');

    $rental->refresh();
    expect($rental->returned_at)->not->toBeNull();
    expect($rental->comment)->toBe('Returned in good condition');
});

test('the comment textarea is actually wired to the comment property', function () {
    // Regression test: the textarea used to bind wire:model="title", a property
    // that doesn't exist on this component, so anything typed by a real user was
    // silently discarded (Livewire created an untracked dynamic property instead)
    // and the comment was never saved - even though a direct ->set('comment', ...)
    // test call would still pass, masking the bug.
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $student = Student::create(['name' => 'Borrower', 'dob' => '2010-01-01', 'gender' => 'male']);
    $rental = Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now()->subDays(2),
        'due_at' => now()->addDays(5),
    ]);

    $html = Livewire::actingAs($user)->test(ReturnBook::class, ['rentalId' => $rental->id])->html();

    expect($html)->toContain("wire:model='comment'");
    expect($html)->not->toContain("wire:model='title'");
});
