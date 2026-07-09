<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Seed the initial activity types (PRD §9 MVP list).
     */
    public function run(): void
    {
        ActivityType::create(['name' => 'Tutoring', 'category' => 'tutoring']);
        ActivityType::create(['name' => 'Extracurricular Training', 'category' => 'extracurricular']);
        ActivityType::create(['name' => 'Mentorship', 'category' => 'mentorship']);
    }
}
