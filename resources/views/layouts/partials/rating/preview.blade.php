@props(['order'])
<div class="mx-auto w-max flex items-center bg-gray-300 pl-12 pr-4 py-3 rounded-full mt-6  ">
    <div
      class="absolute -left-8 w-16 h-16 rounded-full flex items-center justify-center bg-[#facc15] text-[#333] text-sm font-bold">
      {{number_format( $order->feedback->overall_experience,1) }}</div>
    <div class="flex items-center space-x-2">
      @for ($i = 1; $i <= 5; $i++)
        @if ($order->avg_rating >= $i)
          <svg class="w-6 fill-[#facc15]" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M7 0L9.4687 3.60213L13.6574 4.83688L10.9944 8.29787L11.1145 12.6631L7 11.2L2.8855 12.6631L3.00556 8.29787L0.342604 4.83688L4.5313 3.60213L7 0Z" />
          </svg>
        @else
          <svg class="w-6 fill-[#CED5D8]" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M7 0L9.4687 3.60213L13.6574 4.83688L10.9944 8.29787L11.1145 12.6631L7 11.2L2.8855 12.6631L3.00556 8.29787L0.342604 4.83688L4.5313 3.60213L7 0Z" />
          </svg>
        @endif
      @endfor
    </div>
  </div>

