<?php

namespace App\Livewire\DataEntry;

use Livewire\Component;

class AddStudentAttendance extends Component
{
    public function getStudentsProperty()
    {
        return \App\Models\Student::orderBy('name')->get();
    }


    public function render()
    {
        return view('livewire.data-entry.add-student-attendance');
    }
}
