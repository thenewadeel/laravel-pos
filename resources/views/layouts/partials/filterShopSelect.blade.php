<div
    class="form-inline flex flex-wrap md:flex-row justify-items-stretch justify-content-between  m-0 p-2 rounded-md ">

    <?php if (auth()->user()->type == 'admin') {
        $shops = App\Models\Shop::get();
    } else {
        $shops = auth()->user()->shops;
    } ?>
    <div class=" flex flex-col md:flex-row  w-full">
        <div class="ml-2 text-sm font-bold text-gray-700 items-center self-center flex flex-row">
            <div class="">
                {{ __('order.Shops') }}:
            </div>
            <div class="ml-2 rounded-md shadow-slate-400 shadow-inner text-sm font-bold text-gray-700 flex flex-col">
                <div class="mx-1 rounded-md flex items-center p-1 hover:bg-gray-200 cursor-pointer" onclick="selectAll()">
                    <i class="fas fa-check-circle text-green-600 mr-1"></i>
                    <span class="text-sm font-medium text-gray-700 ">All</span>
                </div>
                <div class="mx-1 rounded-md flex items-center p-1 hover:bg-gray-200 cursor-pointer"
                    onclick="selectNone()">
                    <i class="fas fa-times-circle text-red-600 mr-1"></i>
                    <span class="text-sm font-medium text-gray-700 ">None</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col md:flex-row w-full ml-2 p-1 overflow-scroll" id='shop_id_selector'>
            @foreach ($shops as $shop)
                <div
                    class="m-1 rounded-md flex items-center p-1 w-auto {{ in_array($shop->id, request('shop_ids', [])) ? ' bg-blue-300 ' : 'bg-sky-100' }}">
                    <input class="pl-2 form-check-input focus:ring-indigo-500 h-4 w-4 text-indigo-600 " type="checkbox"
                        name="shop_ids[]" value="{{ $shop->id }}" id="shop{{ $shop->id }}"
                        {{ in_array($shop->id, request('shop_ids', [])) ? 'checked' : '' }}>
                    <label class="ml-2 block text-sm font-medium text-gray-700 whitespace-nowrap "
                        for="shop{{ $shop->id }}">
                        {{ $shop->name }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <script type="">
        function selectAll() {
            $('#shop_id_selector input').prop('checked', true);
        }

        function selectNone() {
            $('#shop_id_selector input').prop('checked', false);
        }
    </script>


</div>
