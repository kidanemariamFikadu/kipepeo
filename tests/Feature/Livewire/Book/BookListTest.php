<?php

use App\Livewire\Book\BookList;
use App\Models\Book;
use App\Models\User;
use Livewire\Livewire;

function makeListableBook(string $title = 'Listed Book'): Book
{
    return Book::create(['title' => $title, 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
}

test('an admin can delete a book from the list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = makeListableBook();

    Livewire::actingAs($admin)
        ->test(BookList::class)
        ->call('deleteBook', $book->id);

    expect(Book::find($book->id))->toBeNull();
    expect(Book::withTrashed()->find($book->id))->not->toBeNull();
});

test('a non-admin cannot delete a book', function () {
    $user = User::factory()->create(['role' => 'user']);
    $book = makeListableBook();

    Livewire::actingAs($user)
        ->test(BookList::class)
        ->call('deleteBook', $book->id)
        ->assertForbidden();

    expect(Book::find($book->id))->not->toBeNull();
});

test('the delete button is only rendered for admins, the edit button for everyone', function () {
    $book = makeListableBook();

    $adminHtml = Livewire::actingAs(User::factory()->create(['role' => 'admin']))->test(BookList::class)->html();
    $userHtml = Livewire::actingAs(User::factory()->create(['role' => 'user']))->test(BookList::class)->html();

    expect($adminHtml)->toContain('Delete book');
    expect($userHtml)->not->toContain('Delete book');
    expect($adminHtml)->toContain('Edit book');
    expect($userHtml)->toContain('Edit book');
});
