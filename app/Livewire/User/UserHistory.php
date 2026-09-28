<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class UserHistory extends ModalComponent
{
    public ?User $user;
    public $userAudit;

    /**
     * Guarded here rather than relying on the admin middleware: Livewire
     * only re-applies a fixed allowlist of middleware to /livewire/update,
     * which the app's `admin` middleware is not part of, and the modal host
     * is rendered on every page -- so any logged-in user can otherwise mount
     * this against another account and read its audit trail.
     */
    function mount(User $user)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($user->exists) {
            $this->userAudit = $user->audits()->with('user')->latest()->take(5)->get();
        }
    }
    
    public function render()
    {
        return view('livewire.user.user-history');
    }
}
