<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateStudentForm extends Form
{
    #[Validate('required|max:255')]
    public $name;

    #[Validate('required|in:male,female,other')]
    public $gender;

    #[Validate(['required', 'date'])]
    public $dob;

    #[Validate('required|exists:schools,id')]
    public $school;

    #[Validate('required|exists:grades,id')]
    public $grade;

    #[Validate('required|max:255')]
    public $guardian_name;

    #[Validate('required|max:255|phone:KE')]
    public $guardian_phone;

    protected $messages = [
        'dob.required' => 'The date of birth field is required.',
        // 'dob.before' => 'The student should be 5 and above.',
        'guardian_phone.phone' => 'The guardian phone number is invalid.',
    ];
}
