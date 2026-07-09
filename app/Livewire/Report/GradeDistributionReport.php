<?php

namespace App\Livewire\Report;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use Livewire\Component;

class GradeDistributionReport extends Component
{
    public function render()
    {
        $grades = Grade::withCount([
            'gradeStudents as total_students' => fn ($q) => $q->where('is_current', true),
            'gradeStudents as male_students_count' => fn ($q) => $q->where('is_current', true)
                ->whereHas('student', fn ($sq) => $sq->where('gender', 'Male')),
            'gradeStudents as female_students_count' => fn ($q) => $q->where('is_current', true)
                ->whereHas('student', fn ($sq) => $sq->where('gender', 'Female')),
        ])->orderBy('id')->get();

        $totalEnrolled = Student::whereHas('schools', fn ($q) => $q->where('is_current', true))->count();
        $totalWithGrade = GradeStudent::where('is_current', true)->distinct('student_id')->count('student_id');

        return view('livewire.report.grade-distribution-report', [
            'grades' => $grades,
            'totalEnrolled' => $totalEnrolled,
            'unassignedCount' => max($totalEnrolled - $totalWithGrade, 0),
        ]);
    }
}
