<?php

namespace App\Livewire\Forms\user;

use App\Actions\Fortify\PasswordValidationRules;
use Livewire\Form;

class ResetPasswordForm extends Form
{
    use PasswordValidationRules;

    public $password;
    public $password_confirmation;

    public function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }
}
