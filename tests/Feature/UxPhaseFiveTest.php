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

test('the button component carries the focus ring and dark variants at both sizes', function () {
    // The "+ Add X" buttons in list headers were a second, shorter class
    // string that had lost the focus ring and every dark: variant.
    foreach (['md' => 'px-5 py-2.5', 'sm' => 'px-4 py-2'] as $size => $padding) {
        $html = Blade::render('<x-button size="'.$size.'">Save</x-button>');

        expect($html)->toContain($padding)
            ->toContain('focus:ring-primary-300')
            ->toContain('dark:bg-primary-600')
            ->toContain('disabled:opacity-50');
    }
});

test('no view hand-rolls the primary button or the modal close button', function () {
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

        // Walk <button> tags, finding each tag's real end: a Blade
        // expression in an attribute contains -> and would cut it short.
        $length = strlen($source);
        $offset = 0;

        while (($start = stripos($source, '<button', $offset)) !== false) {
            $quoted = false;
            $end = null;

            for ($i = $start + 7; $i < $length; $i++) {
                if ($source[$i] === '"') {
                    $quoted = ! $quoted;
                } elseif ($source[$i] === '>' && ! $quoted) {
                    $end = $i + 1;
                    break;
                }
            }

            if ($end === null) {
                break;
            }

            $tag = preg_replace('/\s+/', ' ', substr($source, $start, $end - $start));
            $offset = $end;

            // The segmented day-range control and the small inline "Select"
            // in the book search are deliberately their own sizes.
            if (str_contains($tag, 'px-3 py-1') || str_contains($tag, 'rounded-md transition-colors')) {
                continue;
            }

            if (str_contains($tag, 'bg-primary-700 hover:bg-primary-800')
                || str_contains($tag, 'text-gray-400 bg-transparent hover:bg-gray-200')) {
                $offenders[] = $short.': '.substr($tag, 0, 90);
            }
        }
    }

    expect($offenders)->toBe([]);
});

test('every modal still renders a close control', function () {
    // 21 modals had this markup pasted in. If the component were wrong, all
    // 21 would lose their X at once, so assert it renders.
    $html = Blade::render('<x-modal-close wire:click="closeModal" />');

    expect($html)->toContain('Close modal')
        ->toContain('<svg')
        ->toContain('wire:click="closeModal"')
        ->toContain('type="button"');
});

test('no search box or filter select is left without its dark variants', function () {
    // 25 hand-rolled controls carried the component's light classes and none
    // of its dark: ones, so every search box and filter dropdown stayed
    // bright white on a dark page.
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

        preg_match_all('/class="(bg-gray-50[^"]*)"/s', $source, $matches);

        foreach ($matches[1] as $class) {
            if (! str_contains($class, 'dark:')) {
                $offenders[] = $short.': '.substr(preg_replace('/\s+/', ' ', $class), 0, 80);
            }
        }

        // The brand colour is primary; four selects and eight icon buttons
        // had kept Flowbite's blue.
        if (str_contains($source, 'blue-')) {
            $offenders[] = $short.': still uses a blue utility class';
        }
    }

    expect($offenders)->toBe([]);
});

test('the select component matches the input component', function () {
    // x-input existed; every <select> was hand-rolled, which is how the
    // dropdowns drifted away from the text fields.
    $input = Blade::render('<x-input type="text" />');
    $select = Blade::render('<x-select><option>A</option></x-select>');

    foreach (['bg-gray-50', 'dark:bg-gray-700', 'dark:text-white', 'focus:ring-primary-500'] as $class) {
        expect($input)->toContain($class);
        expect($select)->toContain($class);
    }

    expect($select)->toContain('<option>A</option>');
});

test('the auth pages still render after the Jetstream scaffolding was removed', function () {
    // Deleting the unused Jetstream views also orphaned twelve of its
    // components. The auth screens are the ones that still use that
    // scaffolding, so they are what would break.
    foreach (['/login', '/forgot-password'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)->toContain('<form')->toContain('name="email"');
    }
});

test('no view references a deleted component', function () {
    // A missing x-component is a 500 at render time, not a build error, so
    // it only shows up when someone opens that page.
    $components = [];
    foreach (glob(resource_path('views/components/*.blade.php')) as $file) {
        $components[] = basename($file, '.blade.php');
    }
    foreach (glob(resource_path('views/components/*/*.blade.php')) as $file) {
        $components[] = basename(dirname($file)).'.'.basename($file, '.blade.php');
    }

    // Components resolved from classes rather than files.
    $classBacked = ['slot', 'guest-layout'];

    $files = [];
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
    foreach ($dir as $file) {
        if (str_ends_with((string) $file, '.blade.php')) {
            $files[] = (string) $file;
        }
    }

    $missing = [];

    foreach ($files as $file) {
        $source = file_get_contents($file);
        preg_match_all('/<x-([a-z][a-z0-9.-]*)/', $source, $used);

        foreach (array_unique($used[1]) as $name) {
            // Alpine's x-data/x-on/x-ref and friends share the prefix.
            if (in_array($name, ['data', 'on', 'ref', 'show', 'init', 'if', 'for', 'text', 'model', 'cloak', 'transition', 'bind', 'html', 'effect', 'ignore', 'teleport', 'id'], true)) {
                continue;
            }
            if (str_starts_with($name, 'w-') || str_starts_with($name, 'slot')) {
                continue;
            }
            if (in_array($name, $components, true) || in_array($name, $classBacked, true)) {
                continue;
            }

            $missing[] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file).": <x-{$name}";
        }
    }

    expect($missing)->toBe([]);
});
