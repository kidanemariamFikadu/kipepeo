<?php

use App\Livewire\Book\BookDetail;
use App\Models\Book;
use App\Models\User;
use Livewire\Livewire;

test('update persists changes to the book', function () {
    // Regression test: the edit form used to submit to a `create` method that
    // didn't exist on this component at all, so saving an edit always threw
    // a BadMethodCallException instead of updating the book.
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Old Title', 'author' => 'Old Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Story book', 'copies' => 2]);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('title', 'New Title')
        ->set('author', 'New Author')
        ->set('category', 'Grade book')
        ->call('update')
        ->assertHasNoErrors();

    $book->refresh();
    expect($book->title)->toBe('New Title');
    expect($book->author)->toBe('New Author');
    expect($book->category)->toBe('Grade book');
});

test('update requires title, author, and category', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Old Title', 'author' => 'Old Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Story book', 'copies' => 2]);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('title', '')
        ->set('author', '')
        ->set('category', '')
        ->call('update')
        ->assertHasErrors(['title', 'author', 'category']);
});
