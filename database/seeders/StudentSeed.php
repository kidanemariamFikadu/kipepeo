<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Database\Seeder;

class StudentSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolIds = School::pluck('id');
        $gradeIds = Grade::pluck('id');

        if ($schoolIds->isEmpty() || $gradeIds->isEmpty()) {
            $this->call([SchoolSeed::class, GradeSeed::class]);
            $schoolIds = School::pluck('id');
            $gradeIds = Grade::pluck('id');
        }

        for ($i = 0; $i < 40; $i++) {
            $gender = fake()->randomElement(['Male', 'Female']);

            $student = Student::create([
                'name' => fake()->name($gender === 'Male' ? 'male' : 'female'),
                'dob' => fake()->dateTimeBetween('-15 years', '-5 years')->format('Y-m-d'),
                'gender' => $gender,
            ]);

            SchoolStudent::create([
                'student_id' => $student->id,
                'school_id' => $schoolIds->random(),
                'is_current' => true,
            ]);

            GradeStudent::create([
                'student_id' => $student->id,
                'grade' => $gradeIds->random(),
                'is_current' => true,
            ]);

            StudentGuardian::create([
                'student_id' => $student->id,
                'guardian_name' => fake()->name(),
                'guardian_phone' => '07' . fake()->numerify('########'),
                'is_primary' => true,
            ]);

            if (fake()->boolean(40)) {
                StudentGuardian::create([
                    'student_id' => $student->id,
                    'guardian_name' => fake()->name(),
                    'guardian_phone' => '07' . fake()->numerify('########'),
                    'is_primary' => false,
                ]);
            }
        }
    }
}
