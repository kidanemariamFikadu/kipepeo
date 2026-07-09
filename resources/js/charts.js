import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// Shared Chart.js theming + factories for the dashboard and reports. One hue
// per series (never cycled/generated), hairline gridlines, 2px lines, thin
// capped bars - see the project's data-viz conventions.
window.KipepeoCharts = (function () {
    function isDark() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function theme() {
        return isDark() ? {
            text: '#ffffff',
            muted: '#c3c2b7',
            grid: '#2c2c2a',
            series: ['#3987e5', '#199e70', '#c98500', '#008300', '#9085e9', '#e66767', '#d55181', '#d95926'],
        } : {
            text: '#0b0b0b',
            muted: '#52514e',
            grid: '#e1e0d9',
            series: ['#2a78d6', '#1baf7a', '#eda100', '#008300', '#4a3aa7', '#e34948', '#e87ba4', '#eb6834'],
        };
    }

    function baseFont() {
        return { family: 'system-ui, -apple-system, "Segoe UI", sans-serif', size: 12 };
    }

    function tooltipBase() {
        const t = theme();
        return {
            enabled: true,
            mode: 'index',
            intersect: false,
            backgroundColor: isDark() ? '#0d0d0d' : '#ffffff',
            titleColor: t.text,
            bodyColor: t.text,
            borderColor: t.grid,
            borderWidth: 1,
            padding: 10,
            boxPadding: 4,
            titleFont: baseFont(),
            bodyFont: baseFont(),
        };
    }

    function line(canvas, { labels, data, label }) {
        const t = theme();
        return new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label,
                    data,
                    borderColor: t.series[0],
                    backgroundColor: t.series[0] + '1a',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: t.series[0],
                    pointBorderColor: isDark() ? '#1a1a19' : '#fcfcfb',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.25,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipBase(),
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: t.muted, font: baseFont() },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: t.grid, drawTicks: false },
                        border: { display: false },
                        ticks: { color: t.muted, font: baseFont(), precision: 0 },
                    },
                },
            },
        });
    }

    function bar(canvas, { labels, data, horizontal = true, seriesIndex = 0 }) {
        const t = theme();
        const color = t.series[seriesIndex % t.series.length];
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: color,
                    borderRadius: 4,
                    borderSkipped: horizontal ? 'left' : 'bottom',
                    maxBarThickness: 22,
                }],
            },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipBase(),
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: horizontal ? t.grid : 'transparent', drawTicks: false },
                        border: { display: false },
                        ticks: { color: t.muted, font: baseFont(), precision: 0 },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: t.muted, font: baseFont() },
                    },
                },
            },
        });
    }

    function stackedBar(canvas, { labels, datasets }) {
        const t = theme();
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: datasets.map((d, i) => ({
                    ...d,
                    backgroundColor: t.series[i % t.series.length],
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 28,
                })),
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: t.text, font: baseFont(), usePointStyle: true, pointStyle: 'rect' },
                    },
                    tooltip: tooltipBase(),
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: t.grid, drawTicks: false },
                        border: { display: false },
                        ticks: { color: t.muted, font: baseFont(), precision: 0 },
                    },
                    y: {
                        stacked: true,
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: t.text, font: baseFont() },
                    },
                },
            },
        });
    }

    function pie(canvas, { labels, data }) {
        const t = theme();
        return new Chart(canvas, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: labels.map((_, i) => t.series[i % t.series.length]),
                    borderColor: isDark() ? '#1a1a19' : '#fcfcfb',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: t.text, font: baseFont(), usePointStyle: true, pointStyle: 'circle' },
                    },
                    tooltip: tooltipBase(),
                },
            },
        });
    }

    return { isDark, theme, line, bar, stackedBar, pie };
})();
