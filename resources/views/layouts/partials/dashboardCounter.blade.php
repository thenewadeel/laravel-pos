@props([
    'title' => '',
    'value' => 0,
    'value_prefix' => null,
    'route' => '',
    'icon' => 'ion ion-bag',
    'args' => [],
])

<div
    class="bg-lime-100 text-black border-2 border-stone-300 rounded-lg hover:shadow-md shadow-slate-900 h-32 flex flex-col justify-between flex-grow transition-all ">
    <div class="pt-2 px-2  my-0 py-0 ">
        <p class="text-sm font-bold text-gray-600 my-0 py-0">
            {{ $title }}
        </p>
        <hr class="my-0 py-0">
    </div>
    <div class=" px-4 text-xl font-bold items-center align-middle flex flex-row flex-grow justify-between"
        title="{{ $value }}">
        <span class="text-lg text-slate-400 opacity-75 ">{{ $value_prefix }}</span>
        @if ($value < 1000)
            {{ number_format($value, 0) }}
        @elseif ($value < 1000000)
            {{ number_format($value / 1000, 1) }}K
        @else
            {{ number_format($value / 1000000, 1) }}M
        @endif
        <i class="{{ $icon }} text-slate-400 text-5xl  opacity-75">
        </i>
    </div>
    <div class="my-0 py-0 ">
        <hr class="my-0 py-0 ">
        @if ($route)
            <a href="{{ route($route, $args) }}"
                class="text-sm text-blue-800 hover:underline flex flex-row justify-center items-center py-0 my-0">
                {{ __('common.More_info') }}
                <i class="fas fa-arrow-circle-right px-2"></i>
            </a>
        @endif
    </div>
</div>
