<?php

namespace Database\Seeders;

use App\Models\BookCategory;
use Illuminate\Database\Seeder;

class BookCategorySeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(['Story book', 'Supplementary book', 'Grade book', 'Adult novels'])
            ->each(fn ($name) => BookCategory::firstOrCreate(['name' => $name]));
    }
}
