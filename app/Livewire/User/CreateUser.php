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

    /**
     * Guarded here and in create() rather than relying on the admin
     * middleware: Livewire only re-applies a fixed allowlist of middleware
     * to /livewire/update, which the app's `admin` middleware is not part
     * of, and the modal host is rendered on every page -- so any logged-in
     * user can otherwise mount this component and mint themselves an admin.
     */
    function mount()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    function create()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

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
