<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Fills the book-rental gap between the last seeded date and today, so the
 * library page has continuous history for the current term instead of a hole.
 */
class RentalHistorySeed extends Seeder
{
    public function run(): void
    {
        $students = Student::whereNull('graduated_at')->get();
        $books = Book::all();
        $users = User::all();

        if ($students->isEmpty() || $books->isEmpty() || $users->isEmpty()) {
            return;
        }

        $lastSeeded = Rental::max('rented_at');
        $start = $lastSeeded ? Carbon::parse($lastSeeded)->addDay() : Carbon::parse('2026-07-01');
        $today = Carbon::today();

        $cursor = $start->copy();
        while ($cursor->lte($today)) {
            if (! $cursor->isWeekend() && fake()->boolean(45)) {
                $rentalsToday = fake()->numberBetween(1, 3);

                for ($i = 0; $i < $rentalsToday; $i++) {
                    $student = $students->random();
                    $book = $books->random();
                    $rentedAt = $cursor->copy();
                    $dueAt = $rentedAt->copy()->addDays(fake()->numberBetween(7, 14));

                    $returnedAt = null;
                    if ($dueAt->lt($today)) {
                        // Past-due rentals: most were returned, some on time, a few late.
                        if (fake()->boolean(80)) {
                            $returnedAt = fake()->boolean(70)
                                ? $rentedAt->copy()->addDays(fake()->numberBetween(1, $rentedAt->diffInDays($dueAt) ?: 1))
                                : $dueAt->copy()->addDays(fake()->numberBetween(1, 10));

                            if ($returnedAt->gt($today)) {
                                $returnedAt = $today->copy();
                            }
                        }
                    }

                    Rental::create([
                        'book_id' => $book->id,
                        'student_id' => $student->id,
                        'user_id' => $users->random()->id,
                        'rented_at' => $rentedAt->toDateString(),
                        'due_at' => $dueAt->toDateString(),
                        'returned_at' => $returnedAt?->toDateString(),
                    ]);
                }
            }
            $cursor->addDay();
        }
    }
}
