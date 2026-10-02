<?php

use App\Models\Book;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

test('the badge component renders one definition per tone', function () {
    // The same pill class string was retyped at 35 sites and had drifted on
    // font weight and padding. Tone carries the meaning, not the caller.
    $solid = Blade::render('<x-badge tone="success">Available</x-badge>');

    expect($solid)->toContain('bg-green-100', 'text-green-700', 'dark:bg-green-900')
        ->toContain('rounded-full', 'text-xs', 'font-semibold', 'px-2.5')
        ->toContain('Available');

    // The dashboard cards sit on a tinted surface and use a lighter fill.
    expect(Blade::render('<x-badge tone="danger" soft>3d late</x-badge>'))
        ->toContain('bg-red-50', 'dark:bg-red-900/40');

    // Small is the only other size in use.
    expect(Blade::render('<x-badge tone="neutral" size="sm">Inactive</x-badge>'))
        ->toContain('px-2 py-0.5');

    // An unknown tone must still render a readable badge rather than a
    // bare, unstyled span.
    expect(Blade::render('<x-badge tone="nonsense">?</x-badge>'))
        ->toContain('bg-gray-100');
});

test('callers can still add classes without losing the badge styling', function () {
    $html = Blade::render('<x-badge tone="primary" class="ms-2">On loan</x-badge>');

    expect($html)->toContain('ms-2')->toContain('bg-primary-100');
});

test('no view hand-rolls a status pill any more', function () {
    $files = [];
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/livewire')));
    foreach ($dir as $file) {
        if (str_ends_with((string) $file, '.blade.php')) {
            $files[] = (string) $file;
        }
    }

    $offenders = [];

    foreach ($files as $file) {
        $source = file_get_contents($file);

        // A <span> carrying the pill signature: pill-shaped, small text,
        // tinted. Buttons shaped like pills are a different thing and are
        // deliberately left alone.
        preg_match_all('/<span\b[^>]*class="[^"]*"[^>]*>/s', $source, $tags);

        foreach ($tags[0] as $tag) {
            $flat = preg_replace('/\s+/', ' ', $tag);
            if (! str_contains($flat, 'rounded-full')) {
                continue;
            }
            if (! str_contains($flat, 'text-xs') || ! str_contains($flat, 'inline-flex')) {
                continue;
            }
            if (! preg_match('/bg-(primary|green|red|amber|gray)-(50|100)/', $flat)) {
                continue;
            }

            $offenders[] = str_replace(resource_path('views/livewire').DIRECTORY_SEPARATOR, '', $file)
                .': '.substr($flat, 0, 90);
        }
    }

    expect($offenders)->toBe([]);
});

test('the pages that show status badges still render them', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Student::create(['name' => 'Badged Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    Book::create(['title' => 'Badged Book', 'author' => 'A', 'publisher' => 'P', 'copies' => 2]);

    foreach (['/students', '/books', '/users', '/settings/volunteers'] as $path) {
        $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

        expect($html)->toContain('rounded-full');
    }
});
