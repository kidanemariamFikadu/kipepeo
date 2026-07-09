<?php

use App\Livewire\User\UserHistory;
use App\Models\User;
use Livewire\Livewire;
use OwenIt\Auditing\Models\Audit;

// laravel-auditing has `'console' => false` in config/audit.php, so model events
// never actually get audited while running under `php artisan test` (or tinker) -
// these tests create Audit rows directly to exercise the display logic instead of
// relying on the real event pipeline, which can't fire here.
function makeAuditFor(User $target, User $causedBy): Audit
{
    return Audit::create([
        'user_type' => User::class,
        'user_id' => $causedBy->id,
        'event' => 'updated',
        'auditable_type' => User::class,
        'auditable_id' => $target->id,
        'old_values' => ['name' => 'Old Name'],
        'new_values' => ['name' => 'New Name'],
    ]);
}

test('history lists audited changes with the name of who made them', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'user', 'name' => 'Target User']);
    makeAuditFor($target, $admin);

    $html = Livewire::actingAs($admin)->test(UserHistory::class, ['user' => $target])->html();

    expect($html)->toContain($admin->name);
});

test('history does not crash when the auditing user account no longer exists', function () {
    // Regression test: the row used to call App\Models\User::find($audit->user_id)->name
    // directly with no null-safety, so an audit row whose causer account was later
    // deleted would throw "Attempt to read property on null" instead of rendering.
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'user']);
    makeAuditFor($target, $admin);

    $admin->delete();

    $html = Livewire::actingAs($target)->test(UserHistory::class, ['user' => $target->fresh()])->html();

    expect($html)->toContain('Unknown user');
});
