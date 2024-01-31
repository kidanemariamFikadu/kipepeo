<?php

namespace App\Services;

use App\Livewire\Forms\CreateStudentForm;
use App\Models\GradeStudent;
use App\Models\SchoolStudent;
use App\Models\Student;

class StudentService
{
    public static function create(CreateStudentForm $createStudentForm)
    {
        $student = Student::create([
            'name' => $createStudentForm->name,
            'gender' => $createStudentForm->gender,
            'dob' => $createStudentForm->dob,
        ]);

        SchoolStudent::create([
            'student_id' => $student->id,
            'school_id' => $createStudentForm->school,
            'is_current' => true,
        ]);

        $student->guardians()->create([
            'guardian_name' => $createStudentForm->guardian_name,
            'guardian_phone' => $createStudentForm->guardian_phone,
            'is_primary' => true,
        ]);

        GradeStudent::create([
            'student_id' => $student->id,
            'grade' => $createStudentForm->grade,
            'is_current' => true,
        ]);

        return $student;
    }
}
