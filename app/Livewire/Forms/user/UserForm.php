<?php

namespace App\Livewire\Forms\user;

use App\Actions\Fortify\PasswordValidationRules;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    use PasswordValidationRules;

    public $name;
    public $email;
    public $job_title_id;
    public $role;
    public $password;
    public $password_confirmation;

    protected $messages = [
        'job_title_id' => 'The job title field is required.',
    ];

    public function rules(): array
    {
        return [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'job_title_id' => 'required|exists:job_titles,id',
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'password' => $this->passwordRules(),
        ];
    }
}
