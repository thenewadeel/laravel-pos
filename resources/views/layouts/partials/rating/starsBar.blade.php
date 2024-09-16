@props(['rating' => 0])

<div class="mx-auto w-max flex items-center bg-gray-900 pl-12 pr-4 py-3 rounded-full relative mt-6">
    <div
      class="absolute -left-8 w-16 h-16 rounded-full flex items-center justify-center bg-[#facc15] text-[#333] text-xl font-bold">
      {{ number_format($rating, 1) }}</div>
    <div class="flex items-center space-x-2">
      @for ($i = 1; $i <= 5; $i++)
        <svg class="w-6 fill-{{ $i <= $rating ? '[#facc15]' : '[#CED5D8]' }}" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M7 0L9.4687 3.60213L13.6574 4.83688L10.9944 8.29787L11.1145 12.6631L7 11.2L2.8855 12.6631L3.00556 8.29787L0.342604 4.83688L4.5313 3.60213L7 0Z" />
        </svg>
      @endfor
    </div>
  </div>

