<?php

namespace App\Livewire\User;

use App\Livewire\Forms\user\AcceptInviteForm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Accept Invitation')]
#[Layout('layouts.guest')]
class AcceptInvite extends Component
{
    public AcceptInviteForm $form;
    public $token;

    public function mount($token)
    {
        $this->token = $token;
        $this->form->name = "test";
        $this->form->password = "password";
        $this->form->password_confirmation = "password";
    }

    function accept()
    {
        $this->form->validate([
            'name' => 'required|min:3|max:255',
            'password' => 'required|min:8|max:255|confirmed',
        ]);

        $invite = \App\Models\Invite::where([
            'token' => $this->token,
            'status' => 'pending'
        ])->whereDate('expires_at', '>', now())
            ->first();
        if (!$invite) {
            session()->flash('error', 'This invitation is invalid or expired.');
        } else {
            $invite->accept($this->form->name, $this->form->password);
            session()->flash('success', 'You have successfully accepted the invitation.');
            return redirect()->route('login');
        }
    }
}
