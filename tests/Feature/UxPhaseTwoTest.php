<?php

use App\Livewire\Book\BookDetail;
use App\Livewire\Book\BookList;
use App\Livewire\Book\Copies;
use App\Livewire\Report\GradeDistributionReport;
use App\Livewire\Setting\GradeList;
use App\Livewire\Setting\JobTitleList;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function phaseTwoBook(string $title = 'A Book', int $copies = 3): Book
{
    return Book::create([
        'title' => $title, 'author' => 'Author', 'publisher' => 'Pub',
        'class' => 'A', 'category' => 'Fiction', 'copies' => $copies,
    ]);
}

/* -------------------------------------------------- feedback is actually shown */

test('updating a book tells the user it worked', function () {
    // BookDetail flashed a success message that nothing rendered, so clicking
    // Update looked like it had done nothing at all.
    $admin = User::factory()->create(['role' => 'admin']);
    $book = phaseTwoBook('Before Title');

    Livewire::actingAs($admin)
        ->test(BookDetail::class, ['id' => $book->id])
        ->set('title', 'After Title')
        ->call('update')
        ->assertSee('Book updated successfully', false);

    expect($book->fresh()->title)->toBe('After Title');
});

test('marking a copy lost reports back to the user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = phaseTwoBook();
    $copy = BookCopy::create(['book_id' => $book->id, 'status' => 'available']);

    Livewire::actingAs($admin)
        ->test(Copies::class, ['bookId' => $book->id])
        ->call('markAsLost', $copy->id)
        ->assertSee('Copy marked as lost.', false);

    expect($copy->fresh()->status)->toBe('lost');
});

/* ------------------------------------------------------ destructive guardrails */

test('reducing the copy count asks for confirmation naming what is removed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = phaseTwoBook('Shrinking Book', 4);
    foreach (range(1, 4) as $i) {
        BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    }

    $component = Livewire::actingAs($admin)->test(BookDetail::class, ['id' => $book->id]);

    // No confirmation while the count is unchanged.
    expect($component->html())->not->toContain('permanently removes');

    $component->set('copies', 2);
    expect($component->html())->toContain('Reduce copies from 4 to 2?');
    expect($component->html())->toContain('permanently removes 2 available copy record(s)');
});

test('a book that is still on loan cannot be deleted from either screen', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = phaseTwoBook('Lent Book');
    $student = Student::create(['name' => 'Borrower', 'dob' => '2012-01-01', 'gender' => 'male']);
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $admin->id,
        'rented_at' => now()->subDay(), 'due_at' => now()->addWeek(),
    ]);

    Livewire::actingAs($admin)->test(BookList::class)->call('deleteBook', $book->id);
    expect(Book::find($book->id))->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(BookDetail::class, ['id' => $book->id])
        ->call('deleteBook')
        ->assertSee('still has 1 copy on loan', false);

    expect(Book::find($book->id))->not->toBeNull();
});

test('a returned book can still be deleted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = phaseTwoBook('Returned Book');
    $student = Student::create(['name' => 'Borrower', 'dob' => '2012-01-01', 'gender' => 'male']);
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $admin->id,
        'rented_at' => now()->subWeek(), 'due_at' => now()->subDay(), 'returned_at' => now(),
    ]);

    Livewire::actingAs($admin)->test(BookList::class)->call('deleteBook', $book->id);

    expect(Book::find($book->id))->toBeNull();
});

test('removing a guardian, school or grade says it is permanent', function () {
    // None of these models use soft deletes, unlike students and books, so
    // "Remove" really means gone.
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detailed Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    // The Remove button only appears for non-primary guardians.
    \App\Models\StudentGuardian::create([
        'student_id' => $student->id, 'guardian_name' => 'Mercy Guardian',
        'guardian_phone' => '0700111222', 'is_primary' => false,
    ]);

    $html = $this->actingAs($admin)->get("/student-detail/{$student->id}")->getContent();

    expect($html)->toContain('deleted permanently');
    expect($html)->not->toContain('wire:confirm="Remove this school record?"');
    expect($html)->not->toContain('wire:confirm="Remove this grade record?"');
});

/* ------------------------------------------------------------- empty states */

test('settings lists explain themselves when empty', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    expect(Livewire::actingAs($admin)->test(GradeList::class)->html())->toContain('No grades yet');
    expect(Livewire::actingAs($admin)->test(JobTitleList::class)->html())->toContain('No job titles yet');
});

test('the grade distribution report explains an empty table and shows it is working', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $html = Livewire::actingAs($admin)->test(GradeDistributionReport::class)->html();

    expect($html)->toContain('No grades have been set up yet');
    // The school filter re-runs several aggregates, so it reports progress.
    expect($html)->toContain('wire:target="schoolId"');
});

/* --------------------------------------------------------------- wording */

test('recording attendance does not claim a student was created', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Attending Student', 'dob' => '2012-01-01', 'gender' => 'male']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\DataEntry\AddStudentAttendance::class)
        ->set('form.student_id', $student->id)
        ->set('form.date', now()->format('Y-m-d'))
        ->set('form.startTime', '08:00')
        ->set('form.endTime', '12:00')
        ->call('addAttendance')
        ->assertDispatched('student-changed', function ($event, $params) {
            // dispatch() passes the payload positionally.
            return str_contains($params[0]['content'], 'Attendance recorded')
                && ! str_contains($params[0]['content'], 'created');
        });
});
