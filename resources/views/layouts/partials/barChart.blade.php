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
        'limit' => 25,
    ])
    @if (isset($limit))
        @php($graphData = $graphData->slice(0, $limit))
    @endif
    <div class="flex flex-col md:flex-row border border-cyan-600 h-72">
        <div
            class="bg-gray-200 text-center md:text-left md:bg-transparent md:border-b-0 md:border-r-2 md:border-gray-200 p-2 md:pl-4 md:pr-6 flex items-center">
            {{ $chartTitle }}
        </div>
        {{-- <div class="flex-1 p-2 "> --}}
        {{-- <div class="mt-2 "> --}}
        <div class="w-full relative border border-cyan-400" style="position: relative; height:100%; width:100%">
            <canvas id="{{ $chartId }}" class=""></canvas>
        </div>
        {{-- </div> --}}
        {{-- </div> --}}
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(
                document.getElementById('{{ $chartId }}'), {
                    type: 'bar',
                    data: {
                        labels: @json($graphData->keys()),
                        datasets: [
                            @foreach ($dataSets as $dataset)
                                {
                                    label: '{{ $dataset['label'] }}',
                                    backgroundColor: {!! json_encode($dataset['backgroundColor'] ?? 'rgba(54, 162, 235, 0.8)') !!},

                                    borderColor: '{{ $dataset['borderColor'] ?? 'rgba(54, 162, 235, 1)' }}',
                                    type: '{{ $dataset['type'] ?? 'bar' }}',
                                    borderRadius: 5,
                                    data: @json($dataset['data'])
                                },
                            @endforeach
                        ]
                    },
                    options: {
                        indexAxis: '{{ $indexAxis }}',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: '{{ $indexAxis == 'y' ? 'linear' : 'category' }}',
                                title: {
                                    display: !!'{{ $xAxisLabel }}', // Display if label is not empty
                                    text: '{{ $xAxisLabel }}'
                                }
                            },
                            y: {
                                type: '{{ $indexAxis == 'x' ? 'linear' : 'category' }}',
                                title: {
                                    display: !!'{{ $yAxisLabel }}', // Display if label is not empty
                                    text: '{{ $yAxisLabel }}'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₨ ' + context.raw
                                            .toLocaleString(); // Keep currency formatting
                                    }
                                }
                            }
                        }
                    }
                }
            );
        });
    </script>
</div>
