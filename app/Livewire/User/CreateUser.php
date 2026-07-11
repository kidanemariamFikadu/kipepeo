<?php

namespace App\Livewire\User;

use App\Livewire\Forms\user\UserForm;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class CreateUser extends ModalComponent
{
    public UserForm $form;
    function create()
    {
        $this->validate();

        User::create([
            'name' => $this->form->name,
            'email' => $this->form->email,
            'job_title_id' => $this->form->job_title_id,
            'role' => $this->form->role,
            'password' => Hash::make($this->form->password),
            'must_reset_password' => true,
        ]);

        $this->dispatch('user-updated', ['type' => 'success', 'content' => "User created. Share this password with them directly — they'll be asked to set their own on first login."]);
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
