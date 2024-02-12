<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        return [
            'name' => $collection[1],
            'gender' => $collection[2],
            'school' => $collection[3],
            'grade' => $collection[4],
        ];
    }
}
