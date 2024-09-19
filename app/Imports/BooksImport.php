<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BooksImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        return [
            'title' => $collection[1],
            'author' => $collection[2],
            'publisher' => $collection[3],
            'class' => $collection[4],
            'copies' => $collection[4],
        ];
    }
}
