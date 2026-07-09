<?php

namespace App\Livewire\Dashboard;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use Livewire\Component;

class StudentBreakdownChart extends Component
{
    public function render()
    {
        return view('livewire.dashboard.student-breakdown-chart', [
            'gradeChart' => $this->gradeBreakdown(),
            'schoolChart' => $this->schoolBreakdown(),
            'genderChart' => $this->genderBreakdown(),
        ]);
    }

    protected function gradeBreakdown(): array
    {
        $counts = GradeStudent::query()
            ->where('is_current', true)
            ->whereIn('student_id', Student::query()->pluck('id'))
            ->selectRaw('grade, count(*) as total')
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->sortDesc()
            ->take(8);

        $names = Grade::query()->whereIn('id', $counts->keys())->pluck('grade', 'id');

        return [
            'labels' => $counts->keys()->map(fn ($id) => $names[$id] ?? 'Unknown')->values(),
            'data' => $counts->values(),
        ];
    }

    protected function schoolBreakdown(): array
    {
        $counts = SchoolStudent::query()
            ->where('is_current', true)
            ->whereIn('student_id', Student::query()->pluck('id'))
            ->selectRaw('school_id, count(*) as total')
            ->groupBy('school_id')
            ->pluck('total', 'school_id')
            ->sortDesc()
            ->take(8);

        $names = School::query()->whereIn('id', $counts->keys())->pluck('name', 'id');

        return [
            'labels' => $counts->keys()->map(fn ($id) => $names[$id] ?? 'Unknown')->values(),
            'data' => $counts->values(),
        ];
    }

    protected function genderBreakdown(): array
    {
        $counts = Student::query()
            ->selectRaw('gender, count(*) as total')
            ->groupBy('gender')
            ->orderByDesc('total')
            ->pluck('total', 'gender');

        return [
            'labels' => $counts->keys()->map(fn ($g) => ucfirst((string) $g))->values(),
            'data' => $counts->values(),
        ];
    }
}
