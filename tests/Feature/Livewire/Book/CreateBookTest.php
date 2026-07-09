<?php

use App\Livewire\Book\CreateBook;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Livewire\Livewire;

test('creating a book also creates the requested number of book copies', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateBook::class)
        ->set('title', 'New Book')
        ->set('author', 'New Author')
        ->set('publisher', 'New Publisher')
        ->set('class', 'A')
        ->set('category', 'Fiction')
        ->set('copies', 3)
        ->call('create')
        ->assertDispatched('book-changed');

    $book = Book::where('title', 'New Book')->first();
    expect($book)->not->toBeNull();
    expect(BookCopy::where('book_id', $book->id)->count())->toBe(3);
});

test('create validates required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateBook::class)
        ->call('create')
        ->assertHasErrors(['title', 'author', 'category', 'copies']);
});

test('editing an existing book updates its fields without creating new copies', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Old Title', 'author' => 'Old Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 2]);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    Livewire::actingAs($user)
        ->test(CreateBook::class, ['bookId' => $book->id])
        ->set('title', 'Updated Title')
        ->set('author', 'Old Author')
        ->set('publisher', 'Pub')
        ->set('class', 'A')
        ->set('category', 'Fiction')
        ->set('copies', 2)
        ->call('create')
        ->assertDispatched('book-changed');

    expect($book->fresh()->title)->toBe('Updated Title');
    expect(BookCopy::where('book_id', $book->id)->count())->toBe(2);
});
