<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class AttendanceTrendChart extends Component
{
    public int $days = 14;

    public function setDays(int $days): void
    {
        $this->days = in_array($days, [7, 14, 30], true) ? $days : 14;

        $this->dispatch('attendance-trend-updated', chart: $this->chartData());
    }

    #[On('dashboard-changed')]
    public function refreshDashboard(): void
    {
        $this->dispatch('attendance-trend-updated', chart: $this->chartData());
    }

    protected function chartData(): array
    {
        $start = Carbon::today()->subDays($this->days - 1);
        $end = Carbon::today();

        $counts = Attendance::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $labels[] = $day->format('M j');
            $data[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function render()
    {
        return view('livewire.dashboard.attendance-trend-chart', [
            'chart' => $this->chartData(),
        ]);
    }
}
