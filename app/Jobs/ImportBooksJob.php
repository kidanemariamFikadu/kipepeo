<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    var $bookExcel;

    /**
     * Create a new job instance.
     */
    public function __construct($bookExcel)
    {
        $this->bookExcel = $bookExcel;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger(json_encode($this->bookExcel));
        foreach ($this->bookExcel as $books) {
            foreach ($books as $book) {
                if ($book[1]) {
                    $bookExist = Book::where('title', $book[1])->first();
                    if ($bookExist) {
                        $bookExist->update([
                            'title' => $book[1],
                            'author' => $book[2],
                            'publisher' => $book[3],
                            'class' => $book[4],
                            'copies' => $book[5],
                        ]);
                    } else {
                        $newBook =  Book::create([
                            'title' => $book[1],
                            'author' => $book[2],
                            'publisher' => $book[3],
                            'class' => $book[4],
                            'copies' => $book[5],
                        ]);

                        for ($i = 1; $i <= $book[5]; $i++) {
                            BookCopy::create([
                                'book_id' => $newBook->id,
                                'status' => 'available',
                            ]);
                        }
                    }
                }
            }
        }
    }
}
