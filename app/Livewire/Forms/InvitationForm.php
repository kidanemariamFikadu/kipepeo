<?php

namespace App\Livewire\Forms;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Livewire\Form;

class InvitationForm extends Form
{
    public $email;
    public $job_title_id;
    public $role;

    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email|unique:invites,email',
            'job_title_id' => 'required|exists:job_titles,id',
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ];
    }
}
