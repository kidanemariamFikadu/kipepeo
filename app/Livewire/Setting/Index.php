<?php

namespace App\Livewire\Setting;

use App\Models\Grade;
use App\Models\GradeStudent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[On('MessageChanged')]
    public function messageChanged($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[Computed]
    public function getJobTitleListProperty()
    {
        return \App\Models\JobTitle::paginate(10);
    }

    function removeJobTitle($jobTitleId)
    {
        $checkEmployeeExist = \App\Models\User::where('job_title_id', $jobTitleId)->first();
        if ($checkEmployeeExist) {
            session()->flash('error', 'Job Title cannot be deleted as employees are associated with this job title');
            return;
        }

        \App\Models\JobTitle::find($jobTitleId)->delete();
        session()->flash('success', 'Job Title deleted successfully');
    }

    public function promoteStudentsToNextGrade()
    {
        $grades = Grade::orderBy('id')->pluck('id')->values();

        if ($grades->count() < 2) {
            session()->flash('error', 'At least two grades are required to run promotion.');
            return;
        }

        $promotionMap = [];

        foreach ($grades as $index => $gradeId) {
            if (! isset($grades[$index + 1])) {
                continue;
            }

            $promotionMap[(string) $gradeId] = (string) $grades[$index + 1];
        }

        $studentsPromoted = 0;
        $studentsAtFinalGrade = 0;

        DB::transaction(function () use ($promotionMap, &$studentsPromoted, &$studentsAtFinalGrade) {
            $currentGrades = GradeStudent::where('is_current', true)->get();

            foreach ($currentGrades as $currentGrade) {
                $nextGradeId = $promotionMap[(string) $currentGrade->grade] ?? null;

                if (! $nextGradeId) {
                    $studentsAtFinalGrade++;
                    continue;
                }

                $currentGrade->update(['is_current' => false]);

                $nextGrade = GradeStudent::firstOrCreate(
                    [
                        'student_id' => $currentGrade->student_id,
                        'grade' => $nextGradeId,
                    ],
                    [
                        'is_current' => true,
                    ]
                );

                if (! $nextGrade->is_current) {
                    $nextGrade->update(['is_current' => true]);
                }

                $studentsPromoted++;
            }
        });

        session()->flash('success', "Promotion completed. {$studentsPromoted} students promoted, {$studentsAtFinalGrade} remained in final grade.");
    }

    public function render()
    {
        return view('livewire.setting.index')->title('Setting');
    }
}
