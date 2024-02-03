<?php

namespace App\Livewire\User;

use App\Livewire\Forms\InvitationForm;
use App\Models\Invite;
use App\Models\JobTitle;
use App\Services\InvitationSerivce;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Invitation')]
class Invitation extends Component
{
    use WithPagination;
    public InvitationForm $form;


    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $jobTitleId = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 5;

    public function setSortBy($sortByField)
    {

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }


    #[Computed]
    public function getJobTitlesProperty()
    {
        return JobTitle::all();
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

    function render(){
        return view('livewire.user.invitation',[
            'invites' => Invite::search($this->search)
            ->when($this->jobTitleId !== '', function ($query) {
                $query->where('job_title_id', $this->jobTitleId);
            })
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage)
        ]);
    }
}
