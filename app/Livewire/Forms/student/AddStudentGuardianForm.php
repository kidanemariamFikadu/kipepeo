<?php

namespace App\Livewire\Forms\student;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Propaganistas\LaravelPhone\Rules\Phone;

class AddStudentGuardianForm extends Form
{
    #[Validate('required|max:255')]
    public $guardian_name;
    #[Validate('required|max:255|phone:KE')]
    public $guardian_phone;
    #[Validate('required|max:255')]
    public $student_id;
    public $is_primary;

    protected $messages = [
        'guardian_phone.phone' => 'The guardian phone number is invalid.',
    ];
}
