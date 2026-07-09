<?php

namespace App\Livewire\User;

use App\Livewire\Forms\user\UserForm;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class CreateUser extends ModalComponent
{
    public UserForm $form;
    function create()
    {
        $this->validate();

        $user = User::create([
            ...$this->form->toArray(),
            'password' => bcrypt(Str::random(32)),
        ]);

        Password::sendResetLink(['email' => $user->email]);

        $this->dispatch('user-updated', ['type' => 'success', 'content' => 'User created successfully. A password setup link has been emailed to them.']);
        $this->form->reset();
    }

    #[Computed]
    function getJobTitlesProperty()
    {
        return JobTitle::all();
    }
    
    public function render()
    {
        return view('livewire.user.create-user',[
            'jobTitles' => $this->jobTitles,
        ]);
    }
}
