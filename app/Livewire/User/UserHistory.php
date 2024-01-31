<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class UserHistory extends ModalComponent
{
    public ?User $user;
    public $userAudit;

    function mount(User $user)
    {
        if ($user->exists) {
            $this->userAudit = $user->audits()->latest()->take(5)->get();
        }
    }
    
    public function render()
    {
        return view('livewire.user.user-history');
    }
}
