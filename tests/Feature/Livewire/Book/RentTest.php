<?php

use App\Livewire\Book\Rent;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Rental;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function makeRentableBook(int $availableCopies = 1): Book
{
    $book = Book::create(['title' => 'Rentable Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => $availableCopies]);
    for ($i = 0; $i < $availableCopies; $i++) {
        BookCopy::create(['book_id' => $book->id, 'status' => 'available']);
    }

    return $book;
}

function makeStudentWithCurrentSchoolAndGrade(string $name = 'Borrower'): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2010-01-01', 'gender' => 'male']);
    $school = School::create(['name' => $name . ' School']);
    $grade = Grade::create(['grade' => $name . ' Grade']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    return $student;
}

test('renting a book with available copies creates a rental', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);
    $student = makeStudentWithCurrentSchoolAndGrade();

    Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->set('student', $student->id)
        ->set('dueDate', now()->addDays(3)->format('Y-m-d'))
        ->call('rent')
        ->assertDispatched('rental-changed')
        ->assertDispatched('dashboard-changed');

    expect(Rental::where('book_id', $book->id)->where('student_id', $student->id)->exists())->toBeTrue();
});

test('renting a book with no available copies dispatches an error and creates no rental', function () {
    $user = User::factory()->create();
    $book = Book::create(['title' => 'No Copies', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 0]);
    $student = makeStudentWithCurrentSchoolAndGrade();

    Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->set('student', $student->id)
        ->set('dueDate', now()->addDays(3)->format('Y-m-d'))
        ->call('rent')
        ->assertDispatched('rental-changed');

    expect(Rental::where('book_id', $book->id)->exists())->toBeFalse();
});

test('rent requires a student and a due date after today', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);

    Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->set('dueDate', now()->subDay()->format('Y-m-d'))
        ->call('rent')
        ->assertHasErrors(['student', 'dueDate']);
});

test('the student picker only lists students present today', function () {
    // Regression test: this used to unconditionally return every student in the
    // system (dead attendance-streak logic that was never actually applied),
    // so staff could "borrow" a book to a child who wasn't even at the center.
    $user = User::factory()->create();
    $book = makeRentableBook(1);
    $presentToday = makeStudentWithCurrentSchoolAndGrade('Present Today');
    $absentToday = makeStudentWithCurrentSchoolAndGrade('Absent Today');
    $presentYesterday = makeStudentWithCurrentSchoolAndGrade('Present Yesterday');

    \App\Models\Attendance::create(['student_id' => $presentToday->id, 'date' => now(), 'current_in' => true, 'total_time' => 0]);
    \App\Models\Attendance::create(['student_id' => $presentYesterday->id, 'date' => now()->subDay(), 'current_in' => false, 'total_time' => 100]);

    $students = Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->viewData('students');

    expect($students->pluck('name'))->toContain('Present Today');
    expect($students->pluck('name'))->not->toContain('Absent Today');
    expect($students->pluck('name'))->not->toContain('Present Yesterday');
});

test('the student picker also lists alumni regardless of attendance', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);
    $absentToday = makeStudentWithCurrentSchoolAndGrade('Absent Today');
    $alumnus = Student::create(['name' => 'Graduated Alum', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now()]);

    $students = Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->viewData('students');

    expect($students->pluck('name'))->toContain('Graduated Alum');
    expect($students->pluck('name'))->not->toContain('Absent Today');
});

test('mounting Rent with no bookId starts on the book search step', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Rent::class)
        ->assertSet('bookId', null);
});

test('the book search step finds a book by title or author and selectBook reveals the rent form', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);

    $component = Livewire::actingAs($user)->test(Rent::class)->set('bookSearch', 'Rentable');
    expect($component->get('searchableBooks')->pluck('title'))->toContain('Rentable Book');

    $component->call('selectBook', $book->id)->assertSet('bookId', $book->id);
});

test('changeBook resets bookId back to the search step', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);

    Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->call('changeBook')
        ->assertSet('bookId', null);
});

test('the full no-bookId flow can search, select a book, and complete a rental', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);
    $student = makeStudentWithCurrentSchoolAndGrade();

    Livewire::actingAs($user)
        ->test(Rent::class)
        ->set('bookSearch', 'Rentable')
        ->call('selectBook', $book->id)
        ->set('student', $student->id)
        ->set('dueDate', now()->addDays(3)->format('Y-m-d'))
        ->call('rent')
        ->assertDispatched('rental-changed')
        ->assertDispatched('dashboard-changed');

    expect(Rental::where('book_id', $book->id)->where('student_id', $student->id)->exists())->toBeTrue();
});

test('an alumnus can be picked to rent a book', function () {
    $user = User::factory()->create();
    $book = makeRentableBook(1);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    $alumnus = Student::create(['name' => 'Graduated Alum', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now(), 'graduated_grade_id' => $terminalGrade->id]);

    Livewire::actingAs($user)
        ->test(Rent::class, ['bookId' => $book->id])
        ->set('student', $alumnus->id)
        ->set('dueDate', now()->addDays(3)->format('Y-m-d'))
        ->call('rent')
        ->assertDispatched('rental-changed');

    expect(Rental::where('book_id', $book->id)->where('student_id', $alumnus->id)->exists())->toBeTrue();
});
