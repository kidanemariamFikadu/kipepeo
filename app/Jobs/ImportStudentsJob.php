<?php

namespace App\Jobs;

use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Imports\StudentsImport;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $studentExcel;
    /**
     * Create a new job instance.
     */
    public function __construct($studentExcel)
    {
        $this->studentExcel = $studentExcel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger(json_encode($this->studentExcel));
        foreach ($this->studentExcel as $students) {
            foreach ($students as $student) {
                $school = School::where('name', $student[3])->first();
                $grade = Grade::where('grade', $student[4])->first();

                $newStudent =  Student::create([
                    'name' =>ltrim($student[1]),
                    'gender' => $student[2],
                ]);
                if ($school) {
                    $newStudent->schools()->create([
                        'school_id' => $school?->id,
                        'is_current' => true,
                    ]);
                }

                if ($grade) {
                    $newStudent->grades()->create([
                        'grade' => $grade?->id,
                        'is_current' => true,
                    ]);
                }
            }
        }
    }
}
