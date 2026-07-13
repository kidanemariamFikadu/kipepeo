<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-gray-700 dark:text-white text-xl font-semibold">Attendance trend</h2>

        <div class="flex gap-1 bg-gray-100 dark:bg-gray-900 rounded-lg p-1" role="group" aria-label="Date range">
            @foreach ([7 => '7d', 14 => '14d', 30 => '30d'] as $value => $label)
                <button type="button" wire:click="setDays({{ $value }})"
                    wire:loading.attr="disabled" wire:target="setDays({{ $value }})"
                    class="px-3 py-1 text-sm rounded-md transition-colors disabled:opacity-50
                        {{ $days === $value
                            ? 'bg-primary-700 text-white'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div wire:ignore x-data="{
        chart: null,
        render(data) {
            if (this.chart) { this.chart.destroy(); }
            this.chart = KipepeoCharts.line(this.$refs.canvas, { labels: data.labels, data: data.data, label: 'Check-ins' });
        },
    }" x-init="render(@js($chart))"
        @attendance-trend-updated.window="render($event.detail.chart)" class="relative h-64">
        <canvas x-ref="canvas" role="img"
            aria-label="Line chart of daily student check-ins over the selected date range"></canvas>
    </div>

    <p class="sr-only">
        Daily check-ins for the last {{ $days }} days:
        @foreach ($chart['labels'] as $i => $label)
            {{ $label }}: {{ $chart['data'][$i] }}@if (!$loop->last), @endif
        @endforeach
    </p>
</div>
