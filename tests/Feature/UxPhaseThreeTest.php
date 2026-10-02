<?php

use App\Models\Book;
use App\Models\Rental;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\Volunteer;

/* ----------------------------------------------------------------- titles */

test('every routed page sets its own browser-tab title', function () {
    // These all rendered "Kipepeo | Laravel" before, which is unhelpful for
    // the reports staff keep open in tabs.
    $admin = User::factory()->create(['role' => 'admin']);

    $book = Book::create(['title' => 'A Book', 'author' => 'A', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $student = Student::create(['name' => 'A Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    $volunteer = Volunteer::create(['name' => 'A Volunteer', 'status' => 'active']);
    $school = School::create(['name' => 'A School']);

    $expected = [
        '/books' => 'Books',
        "/book-detail/{$book->id}" => 'Book Details',
        '/users' => 'Users',
        // The three detail pages already named themselves after the record,
        // which beats a static label -- left as they were.
        "/student-detail/{$student->id}" => 'A Student Detail',
        "/volunteer-detail/{$volunteer->id}" => 'A Volunteer Detail',
        "/school-detail/{$school->id}" => 'A School Detail',
        '/settings' => 'Settings',
        '/settings/schools' => 'Schools',
        '/settings/grades' => 'Grades',
        '/settings/volunteers' => 'Manage Volunteers',
        '/settings/activity-types' => 'Activity Types',
        '/settings/book-categories' => 'Book Categories',
        '/settings/job-titles' => 'Job Titles',
        '/settings/backup' => 'Backup',
        '/settings/import-students' => 'Import Students',
        '/settings/import-books' => 'Import Books',
        '/reports/enrollment' => 'Enrollment Summary',
        '/reports/grade-distribution' => 'Grade Distribution',
        '/reports/attendance-analytics' => 'Attendance Analytics',
        '/reports/attendance-roster' => 'Daily Attendance Roster',
        '/reports/book-rental' => 'Book &amp; Rental Circulation',
        '/reports/alumni' => 'Alumni Report',
        '/reports/volunteer' => 'Volunteer Activity',
    ];

    foreach ($expected as $path => $title) {
        $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

        expect($html)->toContain("<title>Kipepeo | {$title}</title>");
    }
});

/* ---------------------------------------------------------- active states */

test('the sidebar highlights the page you are actually on', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $volunteer = Volunteer::create(['name' => 'A Volunteer', 'status' => 'active']);
    $school = School::create(['name' => 'A School']);

    // Login lands here, so it must not be a blank sidebar.
    $dashboard = $this->actingAs($admin)->get('/dashboard')->getContent();
    expect($dashboard)->toContain('aria-current="page"');

    // A volunteer's own page belongs to the Volunteers item.
    $volunteerPage = $this->actingAs($admin)->get("/volunteer-detail/{$volunteer->id}")->getContent();
    expect($volunteerPage)->toContain('aria-current="page"');

    // School detail is reached from Settings > Schools, so Admin stays open.
    $schoolPage = $this->actingAs($admin)->get("/school-detail/{$school->id}")->getContent();
    expect($schoolPage)->toContain('aria-current="page"');
});

/* ------------------------------------------------------------ cross-links */

test('books on loan links to both the book and the borrower', function () {
    // This is the screen for chasing an overdue book; it used to give you a
    // name you then had to go and find yourself.
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::create(['title' => 'Lent Book', 'author' => 'A', 'publisher' => 'P', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);
    $student = Student::create(['name' => 'Borrowing Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    Rental::create([
        'book_id' => $book->id, 'student_id' => $student->id, 'user_id' => $admin->id,
        'rented_at' => now()->subDay(), 'due_at' => now()->addWeek(),
    ]);

    $html = $this->actingAs($admin)->get('/books?tab=loan')->getContent();

    expect($html)->toContain(route('book-detail', $book->id));
    expect($html)->toContain(route('student-detail', $student->id));
});

test('the student check-in screen links through to a child record', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Arriving Student', 'dob' => '2012-01-01', 'gender' => 'male']);

    $html = $this->actingAs($user)->get('/attendance')->getContent();

    expect($html)->toContain(route('student-detail', $student->id));
});

/* -------------------------------------------------------------- labelling */

test('the two volunteer screens are named differently', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Settings is for managing the roster...
    $settings = $this->actingAs($admin)->get('/settings/volunteers')->getContent();
    expect($settings)->toContain('Manage Volunteers');

    // ...while the nav's Volunteers item is the daily check-in screen.
    $checkIn = $this->actingAs($admin)->get('/volunteers')->getContent();
    expect($checkIn)->toContain('Volunteers');

    // The settings hub should not offer a second plain "Volunteers" card.
    $hub = $this->actingAs($admin)->get('/settings')->getContent();
    expect($hub)->toContain('Manage Volunteers');
});
