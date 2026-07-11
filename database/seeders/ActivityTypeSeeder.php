<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Seed the real volunteer duties.
     */
    public function run(): void
    {
        ActivityType::create(['name' => 'Cleaning']);
        ActivityType::create(['name' => 'Supervising Students']);
        ActivityType::create(['name' => 'Organizing Books']);
        ActivityType::create(['name' => 'Kipepeo Study Nest']);
        ActivityType::create(['name' => 'Talents']);
        ActivityType::create(['name' => 'Game']);
    }
}
