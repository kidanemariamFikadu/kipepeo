<?php

namespace App\Livewire\User;

use App\Livewire\Forms\user\ResetPasswordForm;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use LivewireUI\Modal\ModalComponent;

class ResetUserPassword extends ModalComponent
{
    public ResetPasswordForm $form;
    public User $user;

    function mount(User $user)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->user = $user;
    }

    function resetPassword()
    {
        $this->validate();

        $this->user->update([
            'password' => Hash::make($this->form->password),
            'must_reset_password' => true,
        ]);

        $this->dispatch('user-updated', ['type' => 'success', 'content' => "Password reset for {$this->user->name}. Share the new password with them directly — they'll be asked to set their own on next login."]);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.user.reset-user-password');
    }
}
