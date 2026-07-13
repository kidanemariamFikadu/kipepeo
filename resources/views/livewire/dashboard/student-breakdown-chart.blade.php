<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md" x-data="{
    tab: 'grade',
    chart: null,
    datasets: {
        grade: @js($gradeChart),
        school: @js($schoolChart),
        gender: @js($genderChart),
    },
    render() {
        if (this.chart) { this.chart.destroy(); }
        const d = this.datasets[this.tab];
        this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: d.labels, data: d.data, horizontal: true });
    },
}" x-init="render()"
    @student-breakdown-updated.window="datasets = { grade: $event.detail.grade, school: $event.detail.school, gender: $event.detail.gender }; render()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-gray-700 dark:text-white text-xl font-semibold">Students by</h2>

        <div class="flex gap-1 bg-gray-100 dark:bg-gray-900 rounded-lg p-1" role="tablist" aria-label="Breakdown">
            @foreach (['grade' => 'Grade', 'school' => 'School', 'gender' => 'Gender'] as $key => $label)
                <button type="button" role="tab" :aria-selected="tab === '{{ $key }}'" @click="tab = '{{ $key }}'; render()"
                    class="px-3 py-1 text-sm rounded-md transition-colors"
                    :class="tab === '{{ $key }}' ? 'bg-primary-700 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div wire:ignore class="relative h-64">
        <canvas x-ref="canvas" role="img" aria-label="Bar chart of student counts for the selected breakdown"></canvas>
    </div>

    <template x-if="datasets[tab].labels.length === 0">
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No data yet.</p>
    </template>
</div>
