<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = ["NOT YET IN SCHOOL", "PLAYGROUP", "PP1", "PP2", "PP3", "GRADE 1", "GRADE 2", "GRADE 3", "GRADE 4", "GRADE 5", "GRADE 6", "GRADE 7", "GRADE 8", "GRADE 9", "GRADE 10", "GRADE 11", "GRADE 12", "FORM 1", "FORM 2", "FORM 3", "FORM 4"];
        foreach ($grades as $grade) {
            \App\Models\Grade::create(['grade' => $grade]);
        }
    }
}
