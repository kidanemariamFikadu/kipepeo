<?php

namespace App\Livewire;

use App\Exports\StudentRegistrationExport;
use App\Livewire\Concerns\HasSortableColumns;
use App\Models\School;
use App\Models\Student;
use App\Services\Reports\StudentRosterQuery;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Students')]
class StudentList extends Component
{
    use HasSortableColumns;
    use WithPagination;

    protected function sortableColumns(): array
    {
        return ['name', 'gender', 'created_at'];
    }

    #[On('student-changed')]
    public function refreshStudents($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    // Deliberately not #[Url]: this searches children by name, and binding
    // it to the query string writes what staff type into the address bar,
    // browser history and the web server's access log.
    public $search = '';

    #[Url(history: true)]
    public $school = '';

    #[Url(history: true)]
    public $sortBy = 'name';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url()]
    public $perPage = 10;

    public $selectedStudents = [];
    public $selectAll = false;

    public function getSchoolListProperty()
    {
        return School::orderBy('name')->get();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Unselect all students if already selected
            $this->selectedStudents = [];
            $this->selectAll = false;
        } else {
            // Select all students on the current page
            $this->selectedStudents = StudentRosterQuery::build($this->rosterFilters())
                ->paginate($this->perPage)->pluck('id')->toArray();
            $this->selectAll = true;
        }
    }

    function deleteSelected()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        Student::destroy($this->selectedStudents);
        $this->reset(['selectedStudents', 'selectAll']);
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Students removed successfully']);
    }

    public function deleteRecord($studentId)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $student = Student::find($studentId);
        $student?->delete();
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Student removed successfully']);
    }

    /**
     * The filter state, in the shape the shared roster query expects. The
     * sort is resolved through the safe helpers rather than read straight
     * off the public properties -- see HasSortableColumns.
     */
    private function rosterFilters(): array
    {
        return [
            'search' => $this->search,
            'school' => $this->school,
            'sortBy' => $this->safeSortBy(),
            'sortDir' => $this->safeSortDir(),
        ];
    }

    /**
     * Name, grade and the day each child's record was created here, for
     * whatever the list is currently filtered to.
     */
    public function exportRegistrations()
    {
        $filters = $this->rosterFilters();

        return Excel::download(
            new StudentRegistrationExport($filters),
            StudentRosterQuery::filename($filters)
        );
    }

    public function render()
    {
        return view('livewire.student-list', [
            'students' => StudentRosterQuery::build($this->rosterFilters())
                ->with([
                    'guardians',
                    'schools' => fn ($query) => $query->where('is_current', true)->with('school'),
                    'grades' => fn ($query) => $query->where('is_current', true)->with('gradeTable'),
                ])
                ->paginate($this->perPage)
        ]);
    }
}
