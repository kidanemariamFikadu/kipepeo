<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasSortableColumns;
use App\Models\School;
use App\Models\Student;
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

    #[On('student-changed')]
    public function refreshStudents($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[Url(history: true)]
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
            $this->selectedStudents = Student::search($this->search)
                ->active()
                ->when($this->school !== '', function ($query) {
                    // $query->where('current_school', $this->admin);
                    $query->whereHas('schools', function ($q) {
                        $q->where('school_id', $this->school)
                            ->where('is_current', true);
                    });
                })
                ->orderBy($this->sortBy, $this->sortDir)
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

    public function render()
    {
        return view('livewire.student-list', [
            'students' => Student::search($this->search)
                ->active()
                ->with([
                    'guardians',
                    'schools' => fn ($query) => $query->where('is_current', true)->with('school'),
                    'grades' => fn ($query) => $query->where('is_current', true)->with('gradeTable'),
                ])
                ->when($this->school !== '', function ($query) {
                    // $query->where('current_school', $this->admin);
                    $query->whereHas('schools', function ($q) {
                        $q->where('school_id', $this->school)
                            ->where('is_current', true);
                    });
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }
}
