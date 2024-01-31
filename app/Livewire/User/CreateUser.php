<?php

namespace App\Livewire\User;

use App\Livewire\Forms\user\UserForm;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class CreateUser extends ModalComponent
{
    public UserForm $form;
    function create()
    {
        $this->validate();
        $user = User::create($this->form->toArray());
        $this->session->flash('success', 'User created successfully.');
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
