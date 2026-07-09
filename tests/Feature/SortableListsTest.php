<?php

use App\Livewire\Book\BookList;
use App\Livewire\StudentList;
use App\Livewire\UserList;
use App\Models\Book;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('setSortBy toggles direction on the same column and resets to DESC on a new column', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(StudentList::class)
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDir', 'ASC')
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'DESC')
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'ASC')
        ->call('setSortBy', 'created_at')
        ->assertSet('sortBy', 'created_at')
        ->assertSet('sortDir', 'DESC');
});

test('user list and book list share the same sort-toggle behavior', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserList::class)
        ->call('setSortBy', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDir', 'DESC');

    Livewire::actingAs($admin)
        ->test(BookList::class)
        ->call('setSortBy', 'author')
        ->assertSet('sortBy', 'author')
        ->assertSet('sortDir', 'DESC');
});

test('sorting the user list by every sortable column does not crash', function () {
    // Regression test: the "Role" column header used to be wired to sort by
    // `is_admin`, a column that doesn't exist on `users` (the real column is
    // `role`), so clicking it threw a SQL error instead of sorting.
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'user']);

    foreach (['name', 'email', 'job_title_id', 'role', 'created_at'] as $column) {
        Livewire::actingAs($admin)
            ->test(UserList::class)
            ->call('setSortBy', $column)
            ->assertOk();
    }
});

test('book list paginates and searches correctly', function () {
    $user = User::factory()->create(['role' => 'user']);
    Book::create(['title' => 'Zebra Book', 'author' => 'A', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    Book::create(['title' => 'Apple Book', 'author' => 'B', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);

    Livewire::actingAs($user)
        ->test(BookList::class)
        ->set('search', 'Zebra')
        ->assertSee('Zebra Book')
        ->assertDontSee('Apple Book');
});
