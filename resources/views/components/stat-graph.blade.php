@props([
    'title',
    'data' => [], // chart data
    'type' => 'bar', // chart type: bar, line, pie, doughnut
    'height' => '300px',
    'id' => 'chart-' . uniqid(),
    'colors' => ['#27548A', '#183B4E', '#DDA853', '#67C090', '#FF3F33', '#8B5DFF'], 
    'subtitle' => null,
])

<div class="shadow-lg card bg-white-secondary">
    <div class="p-6 card-body">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg card-title text-primary">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="text-sm text-base-content/70">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                    <x-dynamic-component component="lucide-more-horizontal" class="w-4 h-4" />
                </div>
            </div>
        </div>

        <div class="relative" style="height: {{ $height }};">
            <canvas id="{{ $id }}"></canvas>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $id }}').getContext('2d');
    const chartData = @json($data);
    const chartType = '{{ $type }}';
    const colors = @json($colors);

    let config = {
        type: chartType,
        data: {
            labels: chartData.labels || [],
            datasets: chartData.datasets || []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    intersect: false
                }
            },
            scales: {}
        }
    };

    if (chartType === 'bar' || chartType === 'line') {
        config.options.scales = {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    }
                }
            }
        };
    }

    // Apply colors to datasets if not already set
    if (chartData.datasets) {
        chartData.datasets.forEach((dataset, index) => {
            if (!dataset.backgroundColor) {
                if (chartType === 'pie' || chartType === 'doughnut') {
                    dataset.backgroundColor = colors;
                    dataset.borderColor = colors.map(color => color);
                    dataset.borderWidth = 2;
                } else {
                    dataset.backgroundColor = colors[index % colors.length] + '20';
                    dataset.borderColor = colors[index % colors.length];
                    dataset.borderWidth = 2;
                    if (chartType === 'line') {
                        dataset.fill = false;
                        dataset.tension = 0.4;
                    }
                }
            }
        });
    }

    const chart = new Chart(ctx, config);

    // Store chart instance globally for export functionality
    window[`chart_${('{{ $id }}').replace('-', '_')}`] = chart;
});

</script>
@endpush