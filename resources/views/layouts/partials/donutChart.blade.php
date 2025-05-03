<div>
    @props([
        'dataSets' => [
            [
                'label' => 'Dataset 1',
                'backgroundColor' => 'rgba(54, 162, 235, 0.8)', // Default blue-ish color with opacity
                'borderColor' => 'rgba(54, 162, 235, 1)', // Default solid blue-ish color
                'data' => collect(),
            ],
        ],
        'graphData' => collect(),
        'chartId' => Str::random(10), // Generate a unique ID if not provided
        'chartTitle' => 'Data Visualization',
        'xAxisLabel' => '',
        'yAxisLabel' => '',
        'indexAxis' => 'y', // Default to horizontal bar chart
        'limit' => null,
    ])
    @if (isset($limit))
        @php($graphData = $graphData->slice(0, $limit)),
    @endif
    <div class="flex flex-col h-full">
        <div
            class="bg-gray-200 text-center md:text-left md:bg-transparent px-2 md:pl-4 md:pr-6 flex items-center h-2 mt-2 -mb-4">
            {{ $chartTitle }}
        </div>
        {{-- <div class="flex-1 p-2 mt-2 "> --}}
        <div class="w-full relative " style="position: relative; height:100%; width:100%">
            <canvas id="{{ $chartId }}" class=""></canvas>
        </div>
        {{-- </div> --}}
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(
                document.getElementById('{{ $chartId }}'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($graphData->keys()),
                        datasets: [{
                            label: '{{ $dataSets[0]['label'] ?? 'Data' }}', // Assuming one main dataset for Doughnut
                            backgroundColor: {!! json_encode($dataSets[0]['backgroundColor'] ?? 'rgba(54, 162, 235, 0.8)') !!},
                            data: @json($graphData->values()->take($limit)), // Apply limit to data values
                            // Add other Doughnut specific dataset options here if needed
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        title: {
                            display: !!'{{ $chartTitle }}',
                            text: '{{ $chartTitle }}'
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'left',
                                labels: {
                                    // color: 'rgb(255, 99, 132)'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₨ ' + context.raw.toLocaleString();
                                    }
                                }
                            },
                            // Add other Doughnut specific options here if needed (e.g., cutoutPercentage)
                        }
                    }
                }
            );
        });
    </script>
</div>
