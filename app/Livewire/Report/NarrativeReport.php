<?php

namespace App\Livewire\Report;

use App\Services\Reports\SnapshotBuilder;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The screen a narrative report is generated from.
 *
 * Generation itself is not wired up yet -- no provider is chosen and no key
 * is configured -- so for now this page does the part that does not need
 * either: it shows exactly which figures would be sent out of the building,
 * and what suppression did to them. For a charity sending data about
 * children to a third party, being able to read the payload before anyone
 * approves the sending is the feature, not a debug view.
 */
#[Title('Narrative Report')]
class NarrativeReport extends Component
{
    /** Donor reports are suppressed; internal staff reports are not. */
    public const TYPES = [
        'donor' => 'Donor / board narrative',
        'staff' => 'Staff insight (internal)',
    ];

    #[Url(history: true)]
    public string $reportType = 'donor';

    #[Url(history: true)]
    public ?string $fromDate = null;

    #[Url(history: true)]
    public ?string $toDate = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        // A board or donor report is written about a month that has ended,
        // so the default period is the last complete one rather than a
        // part-month that will read as a slump.
        $lastMonth = Carbon::now()->subMonthNoOverflow();
        $this->fromDate ??= $lastMonth->copy()->startOfMonth()->toDateString();
        $this->toDate ??= $lastMonth->copy()->endOfMonth()->toDateString();
    }

    public function updatedFromDate(): void
    {
        $this->validateDates();
    }

    public function updatedToDate(): void
    {
        $this->validateDates();
    }

    public function updatedReportType(): void
    {
        $this->validate(['reportType' => 'required|in:'.implode(',', array_keys(self::TYPES))]);
    }

    private function validateDates(): void
    {
        $this->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate',
        ]);
    }

    /**
     * Livewire still renders after a failed validation in an updated hook,
     * with the offending value in place, so render has to cope with dates
     * it would not accept.
     */
    private function periodIsUsable(): bool
    {
        try {
            $from = Carbon::parse($this->fromDate);
            $to = Carbon::parse($this->toDate);
        } catch (\Throwable) {
            return false;
        }

        return $from->lessThanOrEqualTo($to);
    }

    public function setLastFullMonth(): void
    {
        $month = Carbon::now()->subMonthNoOverflow();
        $this->fromDate = $month->copy()->startOfMonth()->toDateString();
        $this->toDate = $month->copy()->endOfMonth()->toDateString();
        $this->resetValidation();
    }

    public function setThisTerm(): void
    {
        // Three months back to yesterday: long enough for a trend to mean
        // something, and never including a part-day of today.
        $this->toDate = Carbon::yesterday()->toDateString();
        $this->fromDate = Carbon::yesterday()->subMonthsNoOverflow(3)->addDay()->toDateString();
        $this->resetValidation();
    }

    /**
     * Change between the two periods, for the figures where a change is
     * meaningful. Null when there is nothing comparable to show -- which is
     * the honest answer whenever the records do not reach back that far.
     */
    private function deltas(array $snapshot): array
    {
        $previous = $snapshot['previous_period']['attendance'] ?? null;

        if ($previous === null || ($previous['visits'] ?? 0) === 0) {
            return [];
        }

        $deltas = [];

        foreach (['visits' => 'Visits', 'unique_students' => 'Children attending', 'average_visit_minutes' => 'Average visit (minutes)'] as $key => $label) {
            $now = $snapshot['attendance'][$key] ?? null;
            $then = $previous[$key] ?? null;

            if ($now === null || $then === null || $then === 0) {
                continue;
            }

            $deltas[] = [
                'label' => $label,
                'now' => $now,
                'then' => $then,
                'change' => $now - $then,
                'percent' => (int) round((($now - $then) / $then) * 100),
            ];
        }

        return $deltas;
    }

    public function render()
    {
        if (! $this->periodIsUsable()) {
            return view('livewire.report.narrative-report', [
                'snapshot' => null,
                'deltas' => [],
                'payloadJson' => null,
                'payloadBytes' => 0,
            ]);
        }

        $builder = $this->reportType === 'staff'
            ? SnapshotBuilder::unsuppressed()
            : new SnapshotBuilder();

        $snapshot = $builder->build(Carbon::parse($this->fromDate), Carbon::parse($this->toDate));
        $payloadJson = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('livewire.report.narrative-report', [
            'snapshot' => $snapshot,
            'deltas' => $this->deltas($snapshot),
            'payloadJson' => $payloadJson,
            'payloadBytes' => strlen($payloadJson),
        ]);
    }
}
