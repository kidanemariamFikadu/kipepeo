<?php

use App\Models\Book;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\Volunteer;

function auditPaths(): array
{
    $student = Student::create(['name' => 'Audit Student', 'dob' => '2012-01-01', 'gender' => 'female']);
    $school = School::create(['name' => 'Audit School']);
    $book = Book::create(['title' => 'Audit Book', 'author' => 'A', 'publisher' => 'P', 'copies' => 2]);
    $volunteer = Volunteer::create(['name' => 'Audit Volunteer', 'email' => 'v@example.com', 'phone' => '0700000000']);

    return [
        'staff' => [
            '/', '/dashboard', '/students', '/attendance', '/volunteers', '/books',
            '/data-entry', '/my-profile',
            "/student-detail/{$student->id}",
            "/book-detail/{$book->id}", "/volunteer-detail/{$volunteer->id}",
            "/reports/alumni", "/reports/attendance-analytics", "/reports/attendance-roster",
            "/reports/book-rental", "/reports/enrollment", "/reports/grade-distribution",
            "/reports/volunteer",
        ],
        'admin' => [
            '/users', '/settings', '/promote-students', "/school-detail/{$school->id}",
            '/settings/activity-types', '/settings/backup', '/settings/book-categories',
            '/settings/grades', '/settings/import-books', '/settings/import-students',
            '/settings/job-titles', '/settings/schools', '/settings/volunteers',
        ],
        'badIds' => [
            '/student-detail/999999', '/school-detail/999999',
            '/book-detail/999999', '/volunteer-detail/999999',
        ],
        'jetstream' => ['/user/profile'],
    ];
}

test('AUDIT: an admin reaches every screen without a server error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $paths = auditPaths();

    $bad = [];
    foreach ([...$paths['staff'], ...$paths['admin']] as $path) {
        $status = $this->actingAs($admin)->get($path)->status();
        if ($status !== 200) {
            $bad[] = "$path -> $status";
        }
    }

    expect($bad)->toBe([]);
});

test('AUDIT: a plain staff account is allowed its screens and refused the admin ones', function () {
    $staff = User::factory()->create(['role' => 'user']);
    $paths = auditPaths();

    $allowed = [];
    foreach ($paths['staff'] as $path) {
        $status = $this->actingAs($staff)->get($path)->status();
        if ($status !== 200) {
            $allowed[] = "$path -> $status (expected 200)";
        }
    }

    $refused = [];
    foreach ($paths['admin'] as $path) {
        $status = $this->actingAs($staff)->get($path)->status();
        if ($status !== 403) {
            $refused[] = "$path -> $status (expected 403)";
        }
    }

    expect($allowed)->toBe([]);
    expect($refused)->toBe([]);
});

test('AUDIT: a stale bookmark 404s rather than showing a stack trace', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $bad = [];
    foreach (auditPaths()['badIds'] as $path) {
        $status = $this->actingAs($admin)->get($path)->status();
        if ($status !== 404) {
            $bad[] = "$path -> $status";
        }
    }

    expect($bad)->toBe([]);
});

test('AUDIT: no registered route renders a 500', function () {
    // /user/profile is registered by Jetstream but its view was deleted long
    // before this audit, so it has always been a crash page.
    $admin = User::factory()->create(['role' => 'admin']);

    $bad = [];
    foreach (auditPaths()['jetstream'] as $path) {
        $status = $this->actingAs($admin)->get($path)->status();
        if ($status >= 500) {
            $bad[] = "$path -> $status";
        }
    }

    expect($bad)->toBe([]);
});
