<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class InvitationForm extends Form
{
    #[Validate('required|email|unique:users,email|unique:invites,email')]
    public $email;
    #[Validate('required|exists:job_titles,id')]
    public $job_title_id;
    #[Validate('required|in:admin,user')]
    public $role;
}
