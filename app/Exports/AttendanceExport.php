<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Services\Reports\AttendanceQuery;
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
 * Attendance as a flat register: one row per sign-in, with the child, their
 * grade, the date and the times in and out.
 *
 * The filters come from the analytics screen the download was started from,
 * through the same AttendanceQuery that screen uses, so the file and the
 * page can never disagree about which rows are in scope.
 */
class AttendanceExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $filters) {}

    public function title(): string
    {
        return 'Attendance';
    }

    public function query()
    {
        return AttendanceQuery::build($this->filters)
            ->with([
                'attrs' => fn ($q) => $q->orderBy('time_in'),
                'student' => fn ($q) => $q->with(['grades' => fn ($g) => $g->where('is_current', true)->with('gradeTable')]),
            ])
            // Joined only to sort by name; student is a belongsTo, so this
            // cannot multiply rows. A stable order also matters because
            // FromQuery reads the result in chunks.
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->orderBy('attendances.date')
            ->orderBy('students.name')
            ->select('attendances.*');
    }

    public function headings(): array
    {
        return ['Name', 'Grade', 'Date', 'Time In', 'Time Out'];
    }

    /**
     * One row per in/out pair. Today every day holds exactly one, but the
     * schema allows a child to sign out and back in, and returning a row per
     * pair means a second visit is never silently dropped from the register.
     *
     * @return array<int, array<int, mixed>>
     */
    public function map($attendance): array
    {
        $name = $attendance->student?->name;
        $grade = $attendance->student?->grades->first()?->gradeTable?->grade ?: 'Unassigned';
        $date = ExcelDate::dateTimeToExcel(Carbon::parse($attendance->date));

        if ($attendance->attrs->isEmpty()) {
            // Marked present with no recorded times. The child still belongs
            // on the register, so emit the row rather than dropping them.
            return [[$name, $grade, $date, null, null]];
        }

        return $attendance->attrs->map(fn ($attr) => [
            $name,
            $grade,
            $date,
            $this->time($attr->time_in),
            // Blank rather than a placeholder: an empty cell reads as "has
            // not signed out", and anything invented here would be a time
            // that never happened.
            $this->time($attr->time_out),
        ])->all();
    }

    private function time(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('H:i');
    }

    public function columnFormats(): array
    {
        // A real date cell, so a pivot table can group by month rather than
        // treating the column as unrelated pieces of text.
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

                // The header stays put while scrolling a long register, and
                // the filter row is what makes this usable without first
                // building a pivot table.
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:E'.$lastRow);
            },
        ];
    }
}
