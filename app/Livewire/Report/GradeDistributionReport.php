<?php

namespace App\Livewire\Report;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\Student;
use Livewire\Component;

class GradeDistributionReport extends Component
{
    public $schoolId = '';

    /**
     * Scopes a gradeStudents/student query to the selected school's current
     * enrollment, shared by every count below so the filter applies
     * everywhere consistently.
     */
    private function scopeToSchool($query)
    {
        return $query->when($this->schoolId, fn ($q) => $q->whereHas(
            'student.schools',
            fn ($sq) => $sq->where('is_current', true)->where('school_id', $this->schoolId)
        ));
    }

    public function render()
    {
        $grades = Grade::withCount([
            'gradeStudents as total_students' => fn ($q) => $this->scopeToSchool($q->where('is_current', true)),
            'gradeStudents as male_students_count' => fn ($q) => $this->scopeToSchool($q->where('is_current', true))
                ->whereHas('student', fn ($sq) => $sq->whereRaw('LOWER(gender) = ?', ['male'])),
            'gradeStudents as female_students_count' => fn ($q) => $this->scopeToSchool($q->where('is_current', true))
                ->whereHas('student', fn ($sq) => $sq->whereRaw('LOWER(gender) = ?', ['female'])),
        ])->orderBy('id')->get();

        $totalEnrolled = Student::whereHas('schools', fn ($q) => $q->where('is_current', true)
            ->when($this->schoolId, fn ($sq) => $sq->where('school_id', $this->schoolId)))
            ->count();
        $totalWithGrade = $this->scopeToSchool(GradeStudent::where('is_current', true))
            ->distinct('student_id')->count('student_id');

        return view('livewire.report.grade-distribution-report', [
            'grades' => $grades,
            'totalEnrolled' => $totalEnrolled,
            'unassignedCount' => max($totalEnrolled - $totalWithGrade, 0),
            'schools' => School::orderBy('name')->get(),
        ]);
    }
}
