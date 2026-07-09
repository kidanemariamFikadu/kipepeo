<?php

namespace App\Livewire\Concerns;

trait ValidatesSpreadsheetUpload
{
    /**
     * extensions: checks the filename; mimetypes: checks the actual file
     * content, so a file renamed to look like a spreadsheet can't pass on
     * its extension alone. text/plain is included because genuine CSV
     * content has no distinct binary signature and is indistinguishable
     * from plain text by content-sniffing.
     */
    public function spreadsheetUploadRules(): array
    {
        return [
            'file',
            'extensions:xlsx,xls,csv',
            'mimetypes:' . implode(',', [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'application/msexcel',
                'application/x-msexcel',
                'text/csv',
                'application/csv',
                'text/plain',
            ]),
        ];
    }
}
