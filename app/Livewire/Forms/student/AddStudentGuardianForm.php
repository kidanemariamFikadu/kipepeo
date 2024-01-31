<?php

namespace App\Livewire\Forms\student;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AddStudentGuardianForm extends Form
{
    #[Validate('required|max:255')]
    public $guardian_name;
    #[Validate('required|max:255')]
    public $guardian_phone;
    #[Validate('required|max:255')]
    public $student_id;
    public $is_primary;
}
