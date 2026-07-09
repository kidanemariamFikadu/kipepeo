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
    /** @var array<int> Grade ids selected for promotion */
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
            ? $this->gradeSummary->whereNotNull('next_grade_id')->pluck('id')->all()
            : [];
    }

    public function promote(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate([
            'selectedGrades' => ['required', 'array', 'min:1'],
        ], [
            'selectedGrades.required' => 'Select at least one grade to promote.',
        ]);

        $grades = Grade::whereIn('id', $this->selectedGrades)->get();

        $promotedCount = 0;
        $skipped = [];

        DB::transaction(function () use ($grades, &$promotedCount, &$skipped) {
            foreach ($grades as $grade) {
                if (! $grade->next_grade_id) {
                    $skipped[] = $grade->grade;
                    continue;
                }

                $currentRecords = GradeStudent::query()
                    ->where('grade', $grade->id)
                    ->where('is_current', true)
                    ->whereIn('student_id', Student::query()->pluck('id'))
                    ->get();

                foreach ($currentRecords as $record) {
                    $record->update(['is_current' => false]);

                    GradeStudent::create([
                        'student_id' => $record->student_id,
                        'grade' => $grade->next_grade_id,
                        'is_current' => true,
                    ]);

                    $promotedCount++;
                }
            }
        });

        $this->selectedGrades = [];
        unset($this->gradeSummary);

        $message = $promotedCount > 0
            ? "{$promotedCount} student(s) promoted successfully."
            : 'No students were promoted.';

        if (! empty($skipped)) {
            $message .= ' Skipped (no next grade configured): '.implode(', ', $skipped).'.';
        }

        session()->flash($promotedCount > 0 ? 'success' : 'error', $message);
    }

    public function render()
    {
        return view('livewire.setting.promote-students');
    }
}
