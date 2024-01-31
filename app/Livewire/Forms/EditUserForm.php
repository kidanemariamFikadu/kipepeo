<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class EditUserForm extends Form
{
    #[Validate('required|max:255|min:3')]
    public $name;
    #[Validate('required|exists:job_titles,id')]
    public $job_title_id;
    #[Validate('required|in:admin,user')]
    public $role;
}
