<?php

namespace App\Livewire\Forms\DataEntry;

use Livewire\Attributes\Validate;
use Livewire\Form;

class AttendanceForm extends Form
{
    #[Validate('required|exists:students,id')]
    public $student_id;
    #[Validate('required|date')]
    public $date;
    #[Validate('required|date_format:H:i')]
    public $startTime;
    #[Validate('required|date_format:H:i|after:startTime')]
    public $endTime;
}
