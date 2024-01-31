<?php

namespace App\Livewire\Forms\student;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AddStudentSchoolForm extends Form
{
    #[Validate('required|exists:schools,id')]
    public $school_id;
    #[Validate('required|exists:students,id')]
    public $student_id;
    #[Validate('required|boolean')]
    public $is_current;
    public $school_student_id;
}
