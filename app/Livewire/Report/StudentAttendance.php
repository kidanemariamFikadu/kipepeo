<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class StudentAttendance extends Component
{
    use WithPagination;

    public $date;
    public $perPage = 10;
    public $students = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->getStudentByDate();
    }

    public function updatedPerPage()
    {
        $this->resetPage('roster-page');
    }

    /**
     * See AttendanceReport::paginate() for why resolveCurrentPage() (not
     * the trait's own getPage()) is used here - it's what keeps this
     * pageName properly initialized on the client.
     */
    private function paginate(Collection $collection, string $pageName): LengthAwarePaginator
    {
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $items = $collection->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator($items, $collection->count(), $this->perPage, $page, ['pageName' => $pageName]);
    }

    function secondsToHms($seconds)
    {
        $seconds = (int) round($seconds ?? 0);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }


    public function getStudentByDate()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        $this->students = Attendance::whereDate('date', $this->date)
            ->whereHas('student')
            ->with([
                'student',
                'student.schools' => fn ($query) => $query->where('is_current', true)->with('school'),
                'student.guardians',
                'attrs',
            ])
            ->get()
            ->sortBy('student.name')
            ->values()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->student->id,
                    'name' => $attendance->student->name,
                    'attributes' => $attendance->attrs->map(function ($attr) {
                        return [
                            'time_in' => $attr->time_in,
                            'time_out' => $attr->time_out,
                        ];
                    }),
                    'current_in' => $attendance->current_in,
                    'total_time' => $this->secondsToHms($attendance->total_time),
                    'school' => $attendance->student->schools->first()?->school?->name ?? 'N/A',
                    'guardians' => $attendance->student->guardians->map(function ($guardian) {
                        return [
                            'guardian_name' => $guardian->guardian_name,
                            'guardian_phone' => $guardian->guardian_phone,
                        ];
                    }),
                ];
            });

        $this->resetPage('roster-page');
    }

    public function render()
    {
        return view('livewire.report.student-attendance', [
            'students' => $this->students,
            'studentsPage' => $this->paginate($this->students, 'roster-page'),
        ]);
    }
}
