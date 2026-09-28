<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire only re-applies an allowlist of middleware to
        // /livewire/update, and `admin` is not on it by default -- so
        // without this, a component first loaded from an admin page keeps
        // answering action calls after its user stops being an admin.
        //
        // This does NOT cover modal components, whose originating request is
        // whatever page the modal was opened from; those carry their own
        // abort_unless() checks.
        Livewire::addPersistentMiddleware([
            EnsureUserIsAdmin::class,
        ]);
    }
}
