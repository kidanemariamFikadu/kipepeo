<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Seeder;

class BookSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Story book', 'Supplementary book', 'Grade book', 'Adult novels'];

        $books = [
            ['title' => 'The Lion and the Jewel', 'author' => 'Wole Soyinka'],
            ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe'],
            ['title' => 'Weep Not, Child', 'author' => 'Ngũgĩ wa Thiong\'o'],
            ['title' => 'The River Between', 'author' => 'Ngũgĩ wa Thiong\'o'],
            ['title' => 'Half of a Yellow Sun', 'author' => 'Chimamanda Ngozi Adichie'],
            ['title' => 'Kidnapped at the Coast', 'author' => 'Rosemarie Owino'],
            ['title' => 'Coming to Birth', 'author' => 'Marjorie Oludhe Macgoye'],
            ['title' => 'The Whale Rider', 'author' => 'Witi Ihimaera'],
            ['title' => 'Facing Mount Kenya', 'author' => 'Jomo Kenyatta'],
            ['title' => 'A Grain of Wheat', 'author' => 'Ngũgĩ wa Thiong\'o'],
            ['title' => 'Blossoms of the Savannah', 'author' => 'Henry Ole Kulet'],
            ['title' => 'Fathers of Nations', 'author' => 'Paul B. Vitta'],
            ['title' => 'Betrayal in the City', 'author' => 'Francis Imbuga'],
            ['title' => 'The Novice', 'author' => 'David Karanja'],
            ['title' => 'New Primary Mathematics 1', 'author' => 'KLB'],
            ['title' => 'New Primary Mathematics 2', 'author' => 'KLB'],
            ['title' => 'Primary English 3', 'author' => 'Oxford University Press'],
            ['title' => 'Junior Secondary Science 1', 'author' => 'Longhorn Publishers'],
            ['title' => 'Junior Secondary Science 2', 'author' => 'Longhorn Publishers'],
            ['title' => 'Social Studies for Primary 4', 'author' => 'Moran Publishers'],
            ['title' => 'Christian Religious Education 5', 'author' => 'Storymoja'],
            ['title' => 'Creative Arts and Sports 2', 'author' => 'Mountain Top Publishers'],
            ['title' => 'Agriculture for Junior Secondary', 'author' => 'East African Educational Publishers'],
            ['title' => 'Home Science for Beginners', 'author' => 'Kenya Literature Bureau'],
            ['title' => 'The Magic Calabash', 'author' => 'Barbara Kimenye'],
        ];

        foreach ($books as $book) {
            $copies = fake()->numberBetween(1, 6);

            $newBook = Book::create([
                'title' => $book['title'],
                'author' => $book['author'],
                'publisher' => fake()->company(),
                'category' => fake()->randomElement($categories),
                'copies' => $copies,
            ]);

            for ($i = 1; $i <= $copies; $i++) {
                BookCopy::create([
                    'book_id' => $newBook->id,
                    'status' => fake()->randomElement(['available', 'available', 'available', 'lost', 'stolen']),
                ]);
            }
        }
    }
}
