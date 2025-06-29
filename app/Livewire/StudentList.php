<?php

namespace App\Livewire;

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
        Student::destroy($this->selectedStudents);
        $this->reset(['selectedStudents', 'selectAll']);
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Students removed successfully']);
    }

    public function setSortBy($sortByField)
    {

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function deleteRecord($studentId)
    {
        $student = Student::find($studentId);
        $student?->delete();
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Student removed successfully']);
    }

    public function render()
    {
        return view('livewire.student-list', [
            'students' => Student::search($this->search)
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
