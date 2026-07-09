<?php

namespace App\Livewire\Setting;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class Grade extends ModalComponent
{
    public $grade;

    public $gradeId;

    public $nextGradeId;

    public function mount($gradeId = null)
    {
        $this->gradeId = $gradeId;
        if ($gradeId) {
            $grade = \App\Models\Grade::find($gradeId);
            $this->grade = $grade->grade;
            $this->nextGradeId = $grade->next_grade_id;
        }
    }

    #[Computed]
    public function otherGrades()
    {
        return \App\Models\Grade::when($this->gradeId, fn ($query) => $query->whereKeyNot($this->gradeId))
            ->orderBy('grade')
            ->get();
    }

    function createGrade()
    {
        $this->validate([
            'grade' => ['required', 'min:3', 'max:255', Rule::unique('grades', 'grade')->ignore($this->gradeId)],
            'nextGradeId' => ['nullable', 'exists:grades,id'],
        ]);

        if ($this->gradeId) {
            $grade = \App\Models\Grade::find($this->gradeId);
            $grade->update(['grade' => $this->grade, 'next_grade_id' => $this->nextGradeId]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Grade updated successfully']);
            $this->dispatch('grade-changed');
            $this->closeModal();
        } else {
            \App\Models\Grade::create(['grade' => $this->grade, 'next_grade_id' => $this->nextGradeId]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Grade created successfully']);
            $this->grade = '';
            $this->nextGradeId = null;
            $this->dispatch('grade-changed');
            $this->closeModal();
        }
    }
    
    public function render()
    {
        return view('livewire.setting.grade');
    }
}
