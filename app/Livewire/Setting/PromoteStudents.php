<?php

namespace App\Livewire\Setting;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Promote Students')]
class PromoteStudents extends Component
{
    /** @var array<int> Grade ids selected for promotion/graduation */
    public array $selectedGrades = [];

    #[Computed]
    public function gradeSummary()
    {
        return Grade::query()
            ->with('nextGrade')
            ->withCount(['gradeStudents as current_students_count' => function ($query) {
                $query->where('is_current', true)
                    ->whereIn('student_id', Student::query()->pluck('id'));
            }])
            ->having('current_students_count', '>', 0)
            ->orderBy('grade')
            ->get();
    }

    public function toggleSelectAll(bool $selectAll): void
    {
        $this->selectedGrades = $selectAll
            ? $this->gradeSummary->pluck('id')->all()
            : [];
    }

    public function promote(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'selectedGrades' => ['required', 'array', 'min:1'],
        ], [
            'selectedGrades.required' => 'Select at least one grade to promote or graduate.',
        ]);

        $grades = Grade::whereIn('id', $this->selectedGrades)->get();

        $promotedCount = 0;
        $graduatedCount = 0;

        DB::transaction(function () use ($grades, &$promotedCount, &$graduatedCount) {
            foreach ($grades as $grade) {
                $currentRecords = GradeStudent::query()
                    ->where('grade', $grade->id)
                    ->where('is_current', true)
                    ->whereIn('student_id', Student::query()->pluck('id'))
                    ->get();

                foreach ($currentRecords as $record) {
                    if ($grade->next_grade_id) {
                        $record->update(['is_current' => false]);

                        GradeStudent::create([
                            'student_id' => $record->student_id,
                            'grade' => $grade->next_grade_id,
                            'is_current' => true,
                        ]);

                        $promotedCount++;
                    } else {
                        Student::find($record->student_id)->graduate($grade->id);

                        $graduatedCount++;
                    }
                }
            }
        });

        $this->selectedGrades = [];
        unset($this->gradeSummary);

        $parts = [];
        if ($promotedCount > 0) {
            $parts[] = "{$promotedCount} student(s) promoted";
        }
        if ($graduatedCount > 0) {
            $parts[] = "{$graduatedCount} student(s) graduated";
        }

        $message = $parts !== []
            ? implode(' and ', $parts).'.'
            : 'No students were promoted or graduated.';

        session()->flash($parts !== [] ? 'success' : 'error', $message);
    }

    public function render()
    {
        return view('livewire.setting.promote-students');
    }
}
