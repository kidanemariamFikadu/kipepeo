<?php

namespace App\Livewire\Report;

use App\Models\Grade;
use App\Models\Student;
use Livewire\Component;

class AlumniReport extends Component
{
    public $fromDate = '';

    public $toDate = '';

    public $gradeId = '';

    public function mount()
    {
        $this->filter();
    }

    public function filter()
    {
        $this->validate([
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'gradeId' => 'nullable|exists:grades,id',
        ]);
    }

    public function render()
    {
        $alumni = Student::query()
            ->whereNotNull('graduated_at')
            ->with(['graduatedGrade', 'schools' => fn ($query) => $query->latest('created_at')->with('school')])
            ->when($this->fromDate, fn ($query) => $query->whereDate('graduated_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('graduated_at', '<=', $this->toDate))
            ->when($this->gradeId, fn ($query) => $query->where('graduated_grade_id', $this->gradeId))
            ->orderByDesc('graduated_at')
            ->get();

        return view('livewire.report.alumni-report', [
            'alumni' => $alumni,
            'totalAlumni' => $alumni->count(),
            'grades' => Grade::orderBy('grade')->get(),
        ]);
    }
}
