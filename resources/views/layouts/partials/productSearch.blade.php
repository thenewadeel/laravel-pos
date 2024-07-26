<div class="flex flex-col ">
    <div class="form-inline justify-content-center">


        <input type="text" class="form-control " placeholder="Search..." oninput="filterProducts()" id="searchInput">
        <button class="btn btn-danger btn-sm btn-delete" onclick="clearSearch()">Clear</button>


        {{-- <button class="btn btn-danger btn-delete" data-url="{{ route('shops.destroy', $shop) }}"><i
                    class="fas fa-trash"></i></button> --}}
    </div>

    {{-- {{ $products->count() }} --}}
    <div id="product-list"
        class=" shadow-[0px_0px_5px] shadow-black flex flex-wrap max-h-[42vh] md:max-h-[38vh] overflow-y-scroll rounded-md">
        @foreach ($products as $product)
            <span data-productname="{{ $product->name }}" style="display:none">
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
                const productName = li[i].dataset?.productname ?? '';
                if (productName.toUpperCase().indexOf(filter) > -1) {
                    li[i].style.display = "contents";
                } else {
                    li[i].style.display = "none";
                }
            }
        }

        function clearSearch() {
            ul = document.getElementById('product-list');
            li = ul.getElementsByTagName('span');
            for (i = 0; i < li.length; i++) {
                li[i].style.display = "none";
            }
            document.getElementById('searchInput').value = '';
        }
    </script>

</div>
