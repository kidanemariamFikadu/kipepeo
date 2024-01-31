<?php

namespace App\Livewire\Forms\student;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AddStudentGradeForm extends Form
{
    #[Validate('required|max:255')]
    public $grade;
    #[Validate('required|exists:students,id')]
    public $student_id;
    #[Validate('required|boolean')]
    public $is_current;
    public $grade_id;
}
