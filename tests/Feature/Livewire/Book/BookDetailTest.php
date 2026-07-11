<?php

use App\Livewire\Book\BookDetail;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Rental;
use App\Models\Student;
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

test('the detail page shows rental history with a borrower and status badge', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Rented Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 1]);
    $student = Student::create(['name' => 'Borrower Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $rental = Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now(),
        'due_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($user)->get("/book-detail/{$book->id}");

    $response->assertSee('Rental History');
    $response->assertSee('Borrower Student');
    $response->assertSee('Borrowed');
    $response->assertSee("rentalId: {$rental->id}", false);
});

test('a returned rental shows the Returned status and no return action', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Returned Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 1]);
    $student = Student::create(['name' => 'Past Borrower', 'dob' => '2010-01-01', 'gender' => 'male']);
    Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now()->subDays(10),
        'due_at' => now()->subDays(3),
        'returned_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($user)->get("/book-detail/{$book->id}");

    $response->assertSee('Returned');
    $response->assertDontSee('Return this book', false);
});

test('growing the copies count creates new available copies', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Growable Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 2]);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('copies', 5)
        ->call('update')
        ->assertHasNoErrors();

    $book->refresh();
    expect($book->copies)->toBe(5);
    expect($book->bookCopies()->count())->toBe(5);
    expect($book->available_copies)->toBe(5);
});

test('shrinking the copies count removes available copies only', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'Shrinkable Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 3]);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('copies', 1)
        ->call('update')
        ->assertHasNoErrors();

    $book->refresh();
    expect($book->copies)->toBe(1);
    expect($book->bookCopies()->count())->toBe(1);
});

test('the copies count cannot be reduced below the number in circulation', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'In Circulation Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 2]);
    BookCopy::create(['book_id' => $book->id, 'status' => 'borrowed']);
    BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('copies', 0)
        ->call('update')
        ->assertHasErrors(['copies']);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('copies', 1)
        ->call('update')
        ->assertHasNoErrors();

    $book->refresh();
    expect($book->copies)->toBe(1);
    expect($book->bookCopies()->count())->toBe(1);
    expect($book->bookCopies()->where('status', 'borrowed')->count())->toBe(1);
});

test('an admin can delete a book from the detail page and is redirected to the book list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::create(['title' => 'Deletable Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 1]);

    Livewire::actingAs($admin)
        ->test(BookDetail::class, ['id' => $book->id])
        ->call('deleteBook')
        ->assertRedirect(route('books'));

    expect(Book::find($book->id))->toBeNull();
});

test('a non-admin cannot delete a book', function () {
    $user = User::factory()->create(['role' => 'user']);
    $book = Book::create(['title' => 'Protected Book', 'author' => 'A', 'category' => 'Story book', 'copies' => 1]);

    Livewire::actingAs($user)
        ->test(BookDetail::class, ['id' => $book->id])
        ->call('deleteBook')
        ->assertForbidden();

    expect(Book::find($book->id))->not->toBeNull();
});
