<?php

namespace App\Livewire\Report;

use App\Models\ActivityType;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Component;

class VolunteerReport extends Component
{
    public $fromDate = '';

    public $toDate = '';

    public $volunteerId = '';

    public $activityTypeId = '';

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
        $this->filter();
    }

    public function filter()
    {
        $this->validate([
            'fromDate' => 'nullable|date',
            'toDate' => 'nullable|date|after_or_equal:fromDate',
            'volunteerId' => 'nullable|exists:volunteers,id',
            'activityTypeId' => 'nullable|exists:activity_types,id',
        ]);
    }

    function secondsToHms($seconds)
    {
        $seconds = (int) round($seconds ?? 0);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    public function render()
    {
        $attendances = VolunteerAttendance::query()
            ->with('volunteer')
            ->when($this->fromDate, fn ($query) => $query->where('date', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->where('date', '<=', $this->toDate))
            ->when($this->volunteerId, fn ($query) => $query->where('volunteer_id', $this->volunteerId))
            ->get();

        // Pay is admin-only. Resolved here rather than hidden in the view:
        // a Blade @if still ships the numbers to the browser in the Livewire
        // payload, where anyone can read them.
        $showPay = (bool) auth()->user()?->isAdmin();

        $hoursByVolunteer = $attendances->groupBy('volunteer_id')
            ->map(function ($rows) use ($showPay) {
                $totalSeconds = $rows->sum('total_time');
                $rate = $showPay ? $rows->first()->volunteer?->hourly_rate : null;

                return [
                    'volunteer' => $rows->first()->volunteer,
                    'totalSeconds' => $totalSeconds,
                    'visits' => $rows->count(),
                    'hourlyRate' => $rate,
                    'estStipend' => $rate ? round($totalSeconds / 3600 * $rate, 2) : null,
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values();

        $activities = VolunteerActivity::query()
            ->with(['activityType', 'volunteer', 'students'])
            ->when($this->fromDate, fn ($query) => $query->where('date', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->where('date', '<=', $this->toDate))
            ->when($this->volunteerId, fn ($query) => $query->where('volunteer_id', $this->volunteerId))
            ->when($this->activityTypeId, fn ($query) => $query->where('activity_type_id', $this->activityTypeId))
            ->get();

        $activityCountsByType = $activities->groupBy('activity_type_id')
            ->map(fn ($rows) => [
                'activityType' => $rows->first()->activityType,
                'count' => $rows->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return view('livewire.report.volunteer-report', [
            'hoursByVolunteer' => $hoursByVolunteer,
            'activityCountsByType' => $activityCountsByType,
            'activityLog' => ($this->volunteerId || $this->activityTypeId) ? $activities->sortByDesc('date')->values() : collect(),
            'totalHoursSeconds' => $attendances->sum('total_time'),
            'totalActivities' => $activities->count(),
            'volunteersActive' => $attendances->pluck('volunteer_id')->unique()->count(),
            'volunteers' => Volunteer::orderBy('name')->get(),
            'activityTypes' => ActivityType::orderBy('name')->get(),
            'showPay' => $showPay,
        ]);
    }
}
