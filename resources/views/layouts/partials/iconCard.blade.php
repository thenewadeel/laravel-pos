<div
    class="mx-2 bg-gray-50 rounded-md rounded-l-3xl mb-2 flex flex-row items-center border-2 border-gray-400  hover:border-green-500  min-w-72">
    <div class=" h-full flex items-center justify-center w-1/5 bg-green-300 bg-opacity-40 rounded-l-3xl">
        <i class="text-lg {{ $icon ?? 'fas fa-home' }}"></i>
    </div>
    <div class="px-2 my-2 border-l-2 border-green-500 flex flex-col items-center w-4/5 ">
        <p class="text-lg font-bold my-0">{{ $title ?? 'Inventory Card' }}</p>
        <div
            class="my-2 p-2 rounded-xl text-center text-xl font-bold border-2 border-gray-50 hover:border-gray-200 text-green-600 hover:shadow-lg">
            {{ $number ?? '22,000' }}</div>
        <p class="text-sm my-0 text-gray-400">{{ $subtitle ?? 'Details' }}</p>
    </div>
</div>
