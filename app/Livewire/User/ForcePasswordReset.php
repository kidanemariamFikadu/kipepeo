<?php

namespace App\Livewire\User;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Set a New Password')]
class ForcePasswordReset extends Component
{
    public $state = [
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function updatePassword(UpdatesUserPasswords $updater)
    {
        $this->resetErrorBag();

        $updater->update(Auth::user(), $this->state);

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_' . Auth::getDefaultDriver() => Auth::user()->getAuthPassword(),
            ]);
        }

        return $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.user.force-password-reset');
    }
}
