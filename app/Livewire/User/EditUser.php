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

    /**
     * Guarded here and in update() rather than relying on the admin
     * middleware: Livewire only re-applies a fixed allowlist of middleware
     * to /livewire/update, which the app's `admin` middleware is not part
     * of, and the modal host is rendered on every page -- so any logged-in
     * user can otherwise mount this component and set their own role.
     */
    function mount(User $user)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($user->exists) {
            $this->form->name = $user->name;
            $this->form->job_title_id = $user->job_title_id;
            $this->form->role = $user->role?->value;
        }
    }

    #[Computed]
    public function getJobTitlesProperty()
    {
        return JobTitle::all();
    }

    function update(){
        abort_unless(auth()->user()?->isAdmin(), 403);

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
