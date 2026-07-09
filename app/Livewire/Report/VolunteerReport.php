<?php

namespace App\Livewire\Report;

use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Component;

class VolunteerReport extends Component
{
    public $fromDate = '';

    public $toDate = '';

    public $volunteerId = '';

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
            ->when($this->fromDate, fn ($query) => $query->whereDate('date', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('date', '<=', $this->toDate))
            ->when($this->volunteerId, fn ($query) => $query->where('volunteer_id', $this->volunteerId))
            ->get();

        $hoursByVolunteer = $attendances->groupBy('volunteer_id')
            ->map(fn ($rows) => [
                'volunteer' => $rows->first()->volunteer,
                'totalSeconds' => $rows->sum('total_time'),
                'visits' => $rows->count(),
            ])
            ->sortByDesc('totalSeconds')
            ->values();

        $activities = VolunteerActivity::query()
            ->with(['activityType', 'volunteer', 'students'])
            ->when($this->fromDate, fn ($query) => $query->whereDate('date', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('date', '<=', $this->toDate))
            ->when($this->volunteerId, fn ($query) => $query->where('volunteer_id', $this->volunteerId))
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
            'activityLog' => $this->volunteerId ? $activities->sortByDesc('date')->values() : collect(),
            'totalHoursSeconds' => $attendances->sum('total_time'),
            'totalActivities' => $activities->count(),
            'volunteersActive' => $attendances->pluck('volunteer_id')->unique()->count(),
            'volunteers' => Volunteer::orderBy('name')->get(),
        ]);
    }
}
