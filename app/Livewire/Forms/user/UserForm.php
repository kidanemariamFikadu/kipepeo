<?php

namespace App\Livewire\Forms\user;

use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    #[Rule('required|min:3|max:255')]
    public $name;
    #[Rule('required|email|unique:users,email')]
    public $email;
    #[Rule('required|exists:job_titles,id')]
    public $job_title_id;
    #[Rule('required|in:admin,user')]
    public $role;

    protected $messages = [
        'job_title_id' => 'The job title field is required.',
    ];
}
