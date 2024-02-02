<?php

namespace App\Livewire\Student;

use App\Livewire\Forms\student\AddStudentGradeForm;
use App\Livewire\Forms\student\AddStudentGuardianForm;
use App\Livewire\Forms\student\AddStudentSchoolForm;
use App\Livewire\Forms\student\UpdateStudentForm;
use App\Models\GradeStudent;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentDetail extends Component
{
    public UpdateStudentForm $updateStudentForm;
    public AddStudentSchoolForm $addStudentSchoolForm;
    public AddStudentGradeForm  $addStudentGradeForm;

    public $studentDetails;
    public $studentId;
    public $studentGuardians;
    public $studentSchools;
    public $studentGrades;

    #[On("student-changed")]
    function updateList()
    {
    }

    function mount()
    {
        $student = Student::find(request()->route('student_id'));//->load('guardians', 'schools', 'grades');
        // $student =  Student::find(request()->route('student_id'))->with(['schools' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }, 'guardians' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }, 'grades' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }])->first();

        if ($student) {
            $this->updateStudentForm->name = $student->name;
            $this->updateStudentForm->dob = $student->dob;
            $this->updateStudentForm->gender = $student->gender;
            $this->studentDetails = $student;
            $this->studentGuardians = StudentGuardian::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            // $this->studentSchools = SchoolStudent::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            $this->studentGrades = GradeStudent::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
        } else {
            abort(404);
        }
    }

    function makeGuardianPrimary($id)
    {
        $guardian = StudentGuardian::findOrFail($id);
        $student = Student::findOrFail($guardian->student_id);
        $student->guardians()->update(['is_primary' => false]);

        $guardian->is_primary = true;
        $guardian->save();
        session()->flash('success', 'Guardian made primary successfully.');
    }

    function deleteGuardian($guardian_id)
    {
        // dd($guardian_id);
        $guardian = StudentGuardian::find($guardian_id);
        $guardian->delete();
        session()->flash('success', 'Guardian deleted successfully.');
        $this->dispatch('student-changed', []);
    }


    function makeSchoolPrimary($student_id, $school_id)
    {
        $school = SchoolStudent::where(['student_id' => $student_id, 'is_current' => true])
            ->update(['is_current' => false]);
        $school = SchoolStudent::where(['school_id' => $school_id, 'student_id' => $student_id])
            ->update(['is_current' => true]);

        session()->flash('success', 'School made primary successfully.');
    }

    function makeGradePrimary($student_id, $grade)
    {
        $school = GradeStudent::where(['student_id' => $student_id, 'is_current' => true])
            ->update(['is_current' => false]);
        $school = GradeStudent::where(['grade' => $grade, 'student_id' => $student_id])
            ->update(['is_current' => true]);

        session()->flash('success', 'Grade made primary successfully.');
    }

    function deleteSchool($student_id, $school_id)
    {
        $school = SchoolStudent::where(['school_id' => $school_id, 'student_id' => $student_id])->delete();
        session()->flash('success', 'School deleted successfully.');
        $this->dispatch('student-changed', []);
    }

    function deleteGrade($student_id, $grade)
    {
        $school = GradeStudent::where(['grade' => $grade, 'student_id' => $student_id])->delete();
        session()->flash('success', 'Grade deleted successfully.');
        $this->dispatch('student-changed', []);
    }

    function update()
    {
        $this->updateStudentForm->validate();
        $student = Student::find($this->studentId);
        $student->update([
            "name" => $this->updateStudentForm->name,
            "gender" => $this->updateStudentForm->gender,
            "dob" => $this->updateStudentForm->dob,
        ]);

        session()->flash('success', 'Updated successfully.');
        $this->dispatch('student-changed', []);
    }

    public function render()
    {
        $student_id = (request()->route('student_id')) ? request()->route('student_id') : $this->studentId;

        // $student = Student::find($student_id)->with(['schools' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }, 'guardians' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }, 'grades' => function ($query) {
        //     $query->orderBy('created_at', 'desc');
        // }])->first();

        $student = Student::find($student_id)->first();


        if ($student) {
            $this->studentId = $student_id;
            $this->studentGuardians = StudentGuardian::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            // $this->studentSchools = SchoolStudent::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            $this->studentGrades = GradeStudent::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            return view('livewire.student.student-detail')->title($student->name . ' Detail');
        } else {
            abort(404);
        }
    }
}
