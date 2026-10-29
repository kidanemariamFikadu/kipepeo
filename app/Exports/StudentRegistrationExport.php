<?php

namespace App\Exports;

use App\Services\Reports\StudentRosterQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
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
/**
 * WithStrictNullComparison is load-bearing, not decoration: without it
 * PhpSpreadsheet compares each value against its null placeholder loosely,
 * and 0 == null in PHP, so every "0 days attended" was skipped and left as
 * a blank cell -- the precise thing the zero in map() exists to avoid.
 */
class StudentRegistrationExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters) {}

    public function title(): string
    {
        return 'Registrations';
    }

    public function query()
    {
        return StudentRosterQuery::build($this->filters)
            ->with(['grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable')])
            // Counted in SQL as one subquery across the whole result rather
            // than a query per child. Distinct dates, not rows: there is one
            // attendance row per child per day today, but counting days is
            // what the column claims, so count days.
            ->withCount([
                'attendances as days_attended' => fn ($q) => $q->select(DB::raw('COUNT(DISTINCT date)')),
            ]);
    }

    public function headings(): array
    {
        // "All Time" because the students list carries no date filter, unlike
        // the attendance export, which is scoped to a chosen period. Without
        // it the two files look like they should agree, and they will not.
        return ['Name', 'Grade', 'Date Registered on System', 'Days Attended (All Time)'];
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
            // A real number, and 0 rather than blank for a child who has
            // never attended: an empty cell would be read as missing data
            // rather than as none.
            (int) ($student->days_attended ?? 0),
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
                $sheet->setAutoFilter('A1:D'.$lastRow);
            },
        ];
    }
}
