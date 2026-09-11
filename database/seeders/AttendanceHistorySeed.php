<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Fills the attendance gap between the last seeded date and today, so the
 * dashboard has continuous history for the current term instead of a hole.
 */
class AttendanceHistorySeed extends Seeder
{
    public function run(): void
    {
        $students = Student::whereNull('graduated_at')->inRandomOrder()->take(30)->get();

        if ($students->isEmpty()) {
            $this->call(StudentSeed::class);
            $students = Student::whereNull('graduated_at')->inRandomOrder()->take(30)->get();
        }

        $lastSeeded = Attendance::max('date');
        $start = $lastSeeded ? Carbon::parse($lastSeeded)->addDay() : Carbon::parse('2026-07-01');
        $today = Carbon::today();

        // Every weekday strictly before today: a completed check-in/out.
        $cursor = $start->copy();
        while ($cursor->lt($today)) {
            if (! $cursor->isWeekend()) {
                foreach ($students as $student) {
                    if (! fake()->boolean(65)) {
                        continue;
                    }

                    if (Attendance::where('student_id', $student->id)->where('date', $cursor->toDateString())->exists()) {
                        continue;
                    }

                    $timeIn = $cursor->copy()->setTime(fake()->numberBetween(7, 8), fake()->numberBetween(0, 59));
                    $timeOut = $timeIn->copy()->addMinutes(fake()->numberBetween(240, 420));

                    $attendance = Attendance::create([
                        'student_id' => $student->id,
                        'date' => $cursor->toDateString(),
                        'current_in' => false,
                        'total_time' => $timeOut->diffInSeconds($timeIn, true),
                    ]);

                    AttendanceAttr::create([
                        'attendance_id' => $attendance->id,
                        'student_id' => $student->id,
                        'date' => $cursor->toDateString(),
                        'time_in' => $timeIn->format('H:i:s'),
                        'time_out' => $timeOut->format('H:i:s'),
                    ]);
                }
            }
            $cursor->addDay();
        }

        // Today, if it's a weekday and not already seeded: most students still
        // checked in, a few already checked out, so live views show both states.
        if (! $today->isWeekend() && ! Attendance::whereDate('date', $today)->exists()) {
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

                    $attendance->update(['total_time' => $timeOut->diffInSeconds($timeIn, true)]);
                }
            }
        }
    }
}
