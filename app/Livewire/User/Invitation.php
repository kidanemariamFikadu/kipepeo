<?php

namespace App\Livewire\User;

use App\Livewire\Forms\InvitationForm;
use App\Models\Invite;
use App\Models\JobTitle;
use App\Services\InvitationSerivce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Invitation')]
class Invitation extends Component
{
    use WithPagination;
    public InvitationForm $form;
    public $search;

    #[Computed]
    public function getJobTitlesProperty()
    {
        return JobTitle::all();
    }

    #[Computed]
    public function getInvitesProperty()
    {
        return Invite::latest()->with('jobTitle','createdBy')
            ->where('email', 'like', "%{$this->search}%")
            ->paginate(5);
    }

    function create()
    {
        $this->validate();
        InvitationSerivce::create(
            $this->form->email,
            $this->form->role,
            $this->form->job_title_id,
            auth()->user()->id
        );
        session()->flash('success', 'Invitation sent successfully.');
        $this->form->reset();
    }

    function sendInvitation($id){
        $invite = Invite::findOrFail($id);
        InvitationSerivce::resend($id);
        session()->flash('success', 'Invitation sent successfully.');
    }

    function deleteInvitation($id){
        $invite = Invite::findOrFail($id);
        $invite->delete();
        session()->flash('success', 'Invitation deleted successfully.');
    }
}
