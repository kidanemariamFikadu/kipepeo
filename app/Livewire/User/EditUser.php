<?php

namespace App\Livewire\User;

use App\Livewire\Forms\EditUserForm;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class EditUser extends ModalComponent
{
    public EditUserForm $form;
    public ?User $user;

    function mount(User $user) 
    {
        if ($user->exists) {
            $this->form->name = $user->name;
            $this->form->job_title_id = $user->job_title_id;
            $this->form->role = $user->role;
        }
    }

    #[Computed]
    public function getJobTitlesProperty()
    {
        return JobTitle::all();
    }

    function update(){
        $this->validate();
        $this->user->update([
            'name' => $this->form->name,
            'job_title_id' => $this->form->job_title_id,
            'role' => $this->form->role,
        ]);
        $this->dispatch('user-updated', ['type' => 'success', 'content' => 'User updated successfully.']);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.user.edit-user');
    }
}
