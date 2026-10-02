<?php

use App\Models\Student;
use App\Models\User;

/**
 * Pulls every <input>/<select> that binds a wire:model out of some rendered
 * HTML and reports the ones with no accessible name — no id paired to a
 * label, no aria-label.
 *
 * @return array<int, string>
 */
function controlsWithoutNames(string $html): array
{
    preg_match_all('/<(input|select)\b[^>]*>/s', $html, $tags);

    // Which ids are actually referenced by a <label for="...">?
    preg_match_all('/<label[^>]*\sfor="([^"]+)"/', $html, $labelled);
    $labelledIds = array_flip($labelled[1]);

    $unnamed = [];

    foreach ($tags[0] as $tag) {
        if (! str_contains($tag, 'wire:model')) {
            continue;
        }
        if (preg_match('/type="(hidden|submit|button)"/', $tag)) {
            continue;
        }
        if (str_contains($tag, 'aria-label=')) {
            continue;
        }

        if (preg_match('/\sid="([^"]+)"/', $tag, $m) && isset($labelledIds[$m[1]])) {
            continue;
        }

        $unnamed[] = substr(preg_replace('/\s+/', ' ', $tag), 0, 110);
    }

    return $unnamed;
}

test('every form control on the main screens has an accessible name', function () {
    // Search boxes were placeholder-only, and the visible labels next to the
    // filter selects had no for=, so they announced nothing and did not focus
    // their control when tapped.
    $admin = User::factory()->create(['role' => 'admin']);
    Student::create(['name' => 'A Student', 'dob' => '2012-01-01', 'gender' => 'male']);

    $paths = [
        '/dashboard', '/students', '/attendance', '/volunteers',
        '/books', '/books?tab=loan', '/users',
        '/settings/schools', '/settings/volunteers', '/promote-students',
        '/reports/enrollment', '/reports/book-rental', '/reports/attendance-analytics',
        '/reports/attendance-roster', '/reports/grade-distribution',
    ];

    $problems = [];

    foreach ($paths as $path) {
        $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

        foreach (controlsWithoutNames($html) as $tag) {
            $problems[] = "{$path}: {$tag}";
        }
    }

    expect($problems)->toBe([]);
});

test('bulk-select checkboxes say what they select', function () {
    // "checkbox, unchecked" with no name, on the control that selects
    // students for deletion.
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Selectable Student', 'dob' => '2012-01-01', 'gender' => 'male']);

    $html = $this->actingAs($admin)->get('/students')->getContent();

    expect($html)->toContain('aria-label="Select all students on this page"');
    expect($html)->toContain('aria-label="Select Selectable Student"');
});

test('report tables mark their header cells as column headers', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach ([
        '/reports/attendance-analytics', '/reports/attendance-roster',
        '/reports/book-rental', '/reports/volunteer', '/reports/alumni',
        '/reports/grade-distribution', '/reports/enrollment',
    ] as $path) {
        $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

        // No bare <th class=...> left: every header declares its scope.
        expect($html)->not->toContain('<th class=');
    }
});

test('the mobile drawer is inert while closed and the hamburger reports its state', function () {
    // The drawer is only translated off-screen, so without inert a phone user
    // tabs through every nav link before reaching the page.
    $admin = User::factory()->create(['role' => 'admin']);

    $html = $this->actingAs($admin)->get('/dashboard')->getContent();

    expect($html)->toContain(':inert="!mobileOpen && !isDesktop"');
    expect($html)->toContain('aria-controls="main-sidebar"');
    expect($html)->toContain(':aria-expanded="mobileOpen');
});

test('no view leaves a text or select control without an accessible name', function () {
    // The route sweep above only sees pages you can navigate to, so it misses
    // every modal -- which is where most of this app's data entry happens.
    // Four labels there pointed at an id that did not exist (two were the
    // typo for="tile"), so they named nothing and did not focus the field when
    // tapped; the attendance date stepper had no label at all.
    $files = [];
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/livewire')));
    foreach ($dir as $file) {
        if (str_ends_with((string) $file, '.blade.php')) {
            $files[] = (string) $file;
        }
    }

    $problems = [];

    foreach ($files as $file) {
        $source = file_get_contents($file);
        $short = str_replace(resource_path('views/livewire').DIRECTORY_SEPARATOR, '', $file);

        preg_match_all('/<label[^>]*\sfor="([^"]+)"/', $source, $labelled);
        $labelledIds = array_flip($labelled[1]);

        preg_match_all('/<(input|select|textarea)\b[^>]*>/s', $source, $tags);

        foreach ($tags[0] as $tag) {
            if (! str_contains($tag, 'wire:model')) {
                continue;
            }
            // Checkboxes are covered by the aria-label assertions above.
            if (preg_match('/type="(hidden|submit|button|checkbox)"/', $tag)) {
                continue;
            }
            if (str_contains($tag, 'aria-label=')) {
                continue;
            }
            if (preg_match('/\sid="([^"]+)"/', $tag, $m) && isset($labelledIds[$m[1]])) {
                continue;
            }

            $problems[] = $short.': '.substr(preg_replace('/\s+/', ' ', $tag), 0, 95);
        }
    }

    expect($problems)->toBe([]);
});
