<div class="flex flex-wrap border-2 border-green-500">
    searching. . .
    <input type="text" class="w-full outline-none bg-white text-gray-600 text-sm px-4 py-3" placeholder="Search..."
        oninput="filterProducts()" id="searchInput">

    {{ $products->count() }}
    <div id="product-list"
        class="border-4 border-green-800 shadow-[0px_0px_5px] shadow-black flex flex-wrap max-h-[42vh] md:max-h-[38vh] overflow-y-scroll rounded-md">
        @foreach ($products as $product)
            <span data-productname="{{ strtolower($product->name) }}" style="display:none" class="max-w-[4em]">
                <livewire:itemCard :product="$product" :order="$order" />
            </span>
        @endforeach
    </div>
    <script>
        function filterProducts() {
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toUpperCase();
            ul = document.getElementById('product-list');
            li = ul.getElementsByTagName('span');
            for (i = 0; i < li.length; i++) {
                // a = li[i].getElementsByTagName('a')[0];
                // txtValue = a.textContent || a.innerText;
                // console.log(li);
                if (li[i].dataset.productname.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "contents";
                } else {
                    li[i].style.display = "none";
                }
            }
        }
    </script>

</div>
