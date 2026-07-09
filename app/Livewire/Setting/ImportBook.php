<?php

namespace App\Livewire\Setting;

use App\Imports\BooksImport;
use App\Jobs\ImportBooksJob;
use App\Livewire\Concerns\ValidatesSpreadsheetUpload;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportBook extends Component
{
    use ValidatesSpreadsheetUpload;
    use WithFileUploads;
    public $books;

    public $booksList;

    public $isLoading;

    public function mount()
    {
        $this->booksList = "No students uploaded";
        $this->isLoading = false;
    }

    function importBooks()
    {
        $this->isLoading = true;

        $this->validate([
            'books' => $this->spreadsheetUploadRules(),
        ]);
        $this->booksList = "staring Uploading";

        $bookExcel = Excel::toArray(new BooksImport, $this->books, \Maatwebsite\Excel\Excel::XLSX);
        
        ImportBooksJob::dispatch($bookExcel);
        $this->booksList = $bookExcel[0];
        $this->isLoading = false;
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Books uploaded started successfully, you will get a notification when the process is complete.']);
    }

    public function render()
    {
        return view('livewire.setting.import-book');
    }
}
