<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

test('the avatar component builds initials and keeps one definition per size', function () {
    // Ten copies of this circle each recomputed the initials inline, and h-9
    // carried text-xs in two of them against text-sm in the other two.
    expect(Blade::render('<x-avatar name="Abbie Block" size="sm" />'))
        ->toContain('AB')
        ->toContain('h-8 w-8 text-xs')
        ->toContain('bg-primary-100');

    expect(Blade::render('<x-avatar name="Mercy Wanjiru Kamau" size="md" tone="warning" />'))
        // Two initials, never three.
        ->toContain('MW')->not->toContain('MWK')
        ->toContain('h-9 w-9 text-sm')
        ->toContain('bg-amber-100');

    // A one-word name still gives one letter rather than blowing up.
    expect(Blade::render('<x-avatar name="Prince" />'))->toContain('P');

    // No name at all renders an empty circle, not the string "null".
    expect(Blade::render('<x-avatar />'))->not->toContain('null');
});

test('the avatar slot carries the in-session dot', function () {
    $html = Blade::render('<x-avatar name="In Session" class="relative"><span class="absolute dot"></span></x-avatar>');

    expect($html)->toContain('IS')
        ->toContain('relative')
        ->toContain('absolute dot');
});

test('no view hand-rolls an initials circle any more', function () {
    $files = [];
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
    foreach ($dir as $file) {
        if (str_ends_with((string) $file, '.blade.php')) {
            $files[] = (string) $file;
        }
    }

    $offenders = [];

    foreach ($files as $file) {
        if (str_ends_with($file, 'avatar.blade.php')) {
            continue;
        }

        $source = file_get_contents($file);

        // The initials expression is the tell: an icon circle has no such
        // thing, so this does not catch the toast and alert circles.
        if (preg_match("/Str::of\(.*?\)->explode\(' '\)->map\(/", $source)) {
            $offenders[] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file);
        }
    }

    expect($offenders)->toBe([]);
});

test('every row in a list carries a wire:key', function () {
    // Without one, Livewire reuses DOM nodes positionally when a list
    // reorders, so a per-row action can land against the wrong record.
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
        $short = str_replace(resource_path('views/livewire').DIRECTORY_SEPARATOR, '', $file);

        if (preg_match_all('/@foreach\s*\([^)]*\)\s*(<(?:tr|li)\b[^>]*>)/s', $source, $matches)) {
            foreach ($matches[1] as $tag) {
                if (! str_contains($tag, 'wire:key')) {
                    $offenders[] = $short.': '.substr(preg_replace('/\s+/', ' ', $tag), 0, 70);
                }
            }
        }
    }

    expect($offenders)->toBe([]);
});

test('keys are unique within a view, so rows cannot collide', function () {
    $files = [];
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/livewire')));
    foreach ($dir as $file) {
        if (str_ends_with((string) $file, '.blade.php')) {
            $files[] = (string) $file;
        }
    }

    $duplicates = [];

    foreach ($files as $file) {
        preg_match_all('/wire:key="([^"]*)"/', file_get_contents($file), $keys);
        $counts = array_count_values($keys[1]);

        foreach ($counts as $key => $count) {
            if ($count > 1) {
                $duplicates[] = str_replace(resource_path('views/livewire').DIRECTORY_SEPARATOR, '', $file).": {$key}";
            }
        }
    }

    expect($duplicates)->toBe([]);
});

test('the screens that show avatars and keyed rows still render', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Student::create(['name' => 'Abbie Block', 'dob' => '2012-01-01', 'gender' => 'female']);

    foreach (['/students', '/users', '/dashboard', '/attendance', '/settings/grades'] as $path) {
        $html = $this->actingAs($admin)->get($path)->assertOk()->getContent();

        expect($html)->toContain('rounded-full');
    }

    // The initials are built by the component now, so prove they arrive.
    expect($this->actingAs($admin)->get('/students')->getContent())->toContain('AB');
});
