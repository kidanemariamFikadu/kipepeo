<?php

namespace App\Exports;

use App\Services\Reports\StudentRosterQuery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One row per child: name, current grade, and the day their record was
 * created in this system.
 *
 * That last column is students.created_at, which is when the record was
 * made here -- not necessarily when the child joined the centre. Where a
 * roster arrived by spreadsheet import, every row carries the date of that
 * import, so the heading says "on System" rather than anything that would
 * read as a join date.
 */
class StudentRegistrationExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters) {}

    public function title(): string
    {
        return 'Registrations';
    }

    public function query()
    {
        return StudentRosterQuery::build($this->filters)
            ->with(['grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable')]);
    }

    public function headings(): array
    {
        return ['Name', 'Grade', 'Date Registered on System'];
    }

    public function map($student): array
    {
        return [
            $student->name,
            $student->grades->first()?->gradeTable?->grade ?: 'Unassigned',
            // A real date cell so the column sorts and groups as a date.
            // created_at is never null for a saved record, but a hand-built
            // row could carry one, and a blank beats the Unix epoch.
            $student->created_at ? ExcelDate::dateTimeToExcel(Carbon::parse($student->created_at)) : null,
        ];
    }

    public function columnFormats(): array
    {
        return ['C' => NumberFormat::FORMAT_DATE_YYYYMMDD2];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max($sheet->getHighestRow(), 1);

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:C'.$lastRow);
            },
        ];
    }
}
