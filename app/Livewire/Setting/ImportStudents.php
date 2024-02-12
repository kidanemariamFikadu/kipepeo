<?php

namespace App\Livewire\Setting;

use App\Imports\StudentsImport;
use App\Jobs\ImportStudentsJob;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportStudents extends Component
{
    use WithFileUploads;
    public $students;

    public $studentsList;

    public $isLoading;

    public function mount()
    {
        $this->studentsList = "No students uploaded";
        $this->isLoading = false;
    }

    function importStudents()
    {
        $this->isLoading = true;

        $this->validate([
            'students' => 'file|extensions:xlsx,xls,csv',
        ]);
        $this->studentsList = "staring Uploading";

        // $value =  Excel::import(new StudentsImport, $this->students);

        $studentExcel = Excel::toArray(new StudentsImport, $this->students, \Maatwebsite\Excel\Excel::XLSX);
        // $this->studentsList = st$udentExcel->first()->count() . " students uploaded";
        ImportStudentsJob::dispatch($studentExcel);
        $this->studentsList = $studentExcel[0];
        $this->isLoading = false;
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Students uploaded started successfully, you will get a notification when the process is complete.']);
    }
    public function render()
    {
        return view('livewire.setting.import-students');
    }
}
