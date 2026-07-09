<?php

use App\Models\Book;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('deleting a student preserves the row and history', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Test Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\StudentList::class)
        ->call('deleteRecord', $student->id);

    expect(Student::find($student->id))->toBeNull();
    expect(Student::withTrashed()->find($student->id))->not->toBeNull();
    expect(Student::withTrashed()->find($student->id)->deleted_at)->not->toBeNull();
});

test('deleting a school preserves the row', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Test School']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Setting\SchoolList::class)
        ->call('removeSchool', $school->id);

    expect(School::find($school->id))->toBeNull();
    expect(School::withTrashed()->find($school->id))->not->toBeNull();
});

test('deleting a grade preserves the row', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'TEST GRADE']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Setting\GradeList::class)
        ->call('removeGrade', $grade->id);

    expect(Grade::find($grade->id))->toBeNull();
    expect(Grade::withTrashed()->find($grade->id))->not->toBeNull();
});

test('book deletion is a soft delete', function () {
    $book = Book::create(['title' => 'Test Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);

    $book->delete();

    expect(Book::find($book->id))->toBeNull();
    expect(Book::withTrashed()->find($book->id))->not->toBeNull();
});
