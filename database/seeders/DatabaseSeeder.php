<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->call(SchoolSeed::class);
        $this->call(GradeSeed::class);
        $this->call(StudentSeed::class);
        $this->call(BookSeed::class);
        $this->call(AttendanceSeed::class);
    }
}
