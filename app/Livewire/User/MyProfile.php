<?php

namespace App\Livewire\User;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Profile')]
class MyProfile extends Component
{
    public function render()
    {
        return view('livewire.user.my-profile');
    }
}
