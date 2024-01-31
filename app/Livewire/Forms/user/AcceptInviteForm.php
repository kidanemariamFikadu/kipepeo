<?php

namespace App\Livewire\Forms\user;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AcceptInviteForm extends Form
{
    #[Validate('required|email|unique:users,email|unique:invites,email')]
    public $email;
    #[Validate('required|max:255|min:3')]
    public $name;
    
    #[Validate('required|min:8|max:255|confirmed')]
    public $password;
    #[Validate('required|min:8|max:255')]
    public $password_confirmation;
}
