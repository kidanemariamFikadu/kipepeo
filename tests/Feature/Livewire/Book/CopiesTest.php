<?php

use App\Livewire\Book\Copies;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Livewire\Livewire;

function makeBookCopy(string $status = 'available'): BookCopy
{
    $book = Book::create(['title' => 'Test Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);

    return BookCopy::create(['book_id' => $book->id, 'status' => $status]);
}

test('admin can mark a copy as lost', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $copy = makeBookCopy('available');

    Livewire::actingAs($admin)
        ->test(Copies::class, ['bookId' => $copy->book_id])
        ->call('markAsLost', $copy->id);

    expect($copy->fresh()->status)->toBe('lost');
});

test('admin can mark a copy as stolen', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $copy = makeBookCopy('available');

    Livewire::actingAs($admin)
        ->test(Copies::class, ['bookId' => $copy->book_id])
        ->call('markAsStolen', $copy->id);

    expect($copy->fresh()->status)->toBe('stolen');
});

test('admin can mark a lost copy back as available', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $copy = makeBookCopy('lost');

    Livewire::actingAs($admin)
        ->test(Copies::class, ['bookId' => $copy->book_id])
        ->call('markAsAvailable', $copy->id);

    expect($copy->fresh()->status)->toBe('available');
});

test('non-admin cannot change a copy status', function () {
    $user = User::factory()->create(['role' => 'user']);
    $copy = makeBookCopy('available');

    Livewire::actingAs($user)
        ->test(Copies::class, ['bookId' => $copy->book_id])
        ->call('markAsLost', $copy->id)
        ->assertForbidden();

    expect($copy->fresh()->status)->toBe('available');
});

test('a copy belonging to a different book cannot be modified through this component', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $copy = makeBookCopy('available');
    $otherBook = Book::create(['title' => 'Other', 'author' => 'A', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);

    $attempt = fn () => Livewire::actingAs($admin)
        ->test(Copies::class, ['bookId' => $otherBook->id])
        ->call('markAsLost', $copy->id);

    expect($attempt)->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    expect($copy->fresh()->status)->toBe('available');
});
