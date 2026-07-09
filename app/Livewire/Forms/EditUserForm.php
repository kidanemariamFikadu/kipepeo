<?php

namespace App\Livewire\Forms;

use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EditUserForm extends Form
{
    public $name;
    public $job_title_id;
    public $role;

    public function rules(): array
    {
        return [
            'name' => 'required|max:255|min:3',
            'job_title_id' => 'required|exists:job_titles,id',
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ];
    }
}
