<?php

namespace App\Livewire\Forms\student;

use Livewire\Attributes\Validate;
use Livewire\Form;

class UpdateStudentForm extends Form
{
    #[Validate('required|max:255')]
    public $name;
    #[Validate('required|in:male,female,other')]
    public $gender;
    #[Validate('required|date')]
    public $dob;
    public $student_id;
}
