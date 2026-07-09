<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::inRandomOrder()->take(25)->get();

        if ($students->isEmpty()) {
            $this->call(StudentSeed::class);
            $students = Student::inRandomOrder()->take(25)->get();
        }

        // Past 9 weekdays (excluding today): each attending student gets one
        // completed check-in/check-out for the day, with an accumulated total_time.
        $pastDays = collect();
        $cursor = Carbon::yesterday();
        while ($pastDays->count() < 9) {
            if (! $cursor->isWeekend()) {
                $pastDays->push($cursor->copy());
            }
            $cursor->subDay();
        }

        foreach ($students as $student) {
            foreach ($pastDays as $day) {
                if (! fake()->boolean(75)) {
                    continue;
                }

                $timeIn = $day->copy()->setTime(fake()->numberBetween(7, 8), fake()->numberBetween(0, 59));
                $timeOut = $timeIn->copy()->addMinutes(fake()->numberBetween(240, 420));

                $attendance = Attendance::create([
                    'student_id' => $student->id,
                    'date' => $day->toDateString(),
                    'current_in' => false,
                    'total_time' => $timeOut->diffInSeconds($timeIn),
                ]);

                AttendanceAttr::create([
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                    'date' => $day->toDateString(),
                    'time_in' => $timeIn->format('H:i:s'),
                    'time_out' => $timeOut->format('H:i:s'),
                ]);
            }
        }

        // Today: most of the seeded students are checked in and still on-site,
        // a few have already checked out, so the live attendance page has both states.
        $today = Carbon::today();
        foreach ($students->take(15) as $index => $student) {
            $timeIn = $today->copy()->setTime(fake()->numberBetween(7, 8), fake()->numberBetween(0, 59));
            $stillIn = $index % 4 !== 0;

            $attendance = Attendance::create([
                'student_id' => $student->id,
                'date' => $today->toDateString(),
                'current_in' => $stillIn,
                'total_time' => 0,
            ]);

            if ($stillIn) {
                AttendanceAttr::create([
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                    'date' => $today->toDateString(),
                    'time_in' => $timeIn->format('H:i:s'),
                    'time_out' => null,
                ]);
            } else {
                $timeOut = $timeIn->copy()->addMinutes(fake()->numberBetween(120, 240));

                AttendanceAttr::create([
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                    'date' => $today->toDateString(),
                    'time_in' => $timeIn->format('H:i:s'),
                    'time_out' => $timeOut->format('H:i:s'),
                ]);

                $attendance->update(['total_time' => $timeOut->diffInSeconds($timeIn)]);
            }
        }
    }
}
