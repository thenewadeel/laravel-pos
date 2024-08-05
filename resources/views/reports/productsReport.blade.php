@extends('layouts.admin')

@section('title', __('common.Products_Report'))
@section('content-header', __('common.Products_Report'))

@section('content')
    <div class="card">
        <div class="card-header font-bold text-lg">
            Filters
        </div>
        <div class="card-body">
            {{-- {{ $orders }}
            <hr />
            {{ $openOrders }} --}}
            <form class="mb-4" action="{{ route('reports.productsReport') }}" method="GET">
                <div class="col items-end px-4">
                    @include('layouts.partials.dateRangeFormGroup', [
                        'name' => 'dateRange',
                        'start_date' => request('start_date', date('Y-m-d')),
                        'end_date' => request('end_date', date('Y-m-d')),
                        // 'stylingClasses' => 'col',
                    ])
                    <div class="form-group">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="shop_id">Shops :</label>
                            <div class="form-inline">
                                <button class="btn btn-sm btn-outline-info" type="button"
                                    onclick="selectAllCheckboxes(this)">Select All</button>
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                    onclick="deselectAllCheckboxes(this)">Select None</button>
                            </div>
                        </div>
                        <div class="form-inline">
                            @foreach ($shops as $shop)
                                <div class="form-check form-check-inline badge-info p-1 m-1 rounded-lg">
                                    <input class="form-check-input" type="checkbox" name="shops[]"
                                        value="{{ $shop->id }}" id="shop{{ $shop->id }}"
                                        {{ in_array($shop->id, request()->query('shops', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="shop{{ $shop->id }}">
                                        {{ $shop->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <script>
                            function selectAllCheckboxes(selectAllButton) {
                                var checkboxes = document.querySelectorAll('input[name="shops[]"]');
                                checkboxes.forEach((checkbox) => {
                                    checkbox.checked = true;
                                });
                                selectAllButton.disabled = true;
                            }

                            function deselectAllCheckboxes(deselectAllButton) {
                                var checkboxes = document.querySelectorAll('input[name="shops[]"]');
                                checkboxes.forEach((checkbox) => {
                                    checkbox.checked = false;
                                });
                                deselectAllButton.parentNode.querySelector('button[type="button"]').disabled = false;
                            }
                        </script>
                    </div>

                    <div class="row">
                        <button type="submit" class="btn btn-primary col-md-8">{{ __('common.Filter') }}</button>
                        <a href="{{ route('reports.dailySale') }}"
                            class="btn btn-danger col-md-4">{{ __('common.Clear') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header font-bold text-lg">
            <div class=" flex flex-row justify-between">
                QCL - POS Products Sale Report

                <button onclick="exportToExcel('salesTable', 'Sales Report {{ request('date', date('Y-m-d')) }}')"
                    class="btn btn-dark btn-sm border-2 border-green-800 text-black">{{ __('common.Download_To_Excel') }}</button>
            </div>
        </div>
        <div class="card-body">
            <table id="salesTable" class="table table-bordered table-striped table-hover table-sm">
                <thead style="display:none">
                    {{-- style="display:none"> --}}

                    <tr></tr>
                </thead>

                <?php
                // $grandTotalSoldQty = 0;
                // $grandTotalSoldAmount = 0;
                ?>
                {{-- @foreach ($orderItems->pluck('product.name') as $item) --}}
                {{-- @if ($shopOrders = collect($orders)->where('shop_id', $shop->id)->sortBy('date')->sortBy('time')) --}}
                <thead>
                    <tr class="table-primary">
                        <td colspan="5" class="text-center font-serif font-bold text-lg">
                            QCL - POS Products Sale Report
                        </td>
                    </tr>
                    <tr class="table-striped">
                        <td class="font-bold text-center">
                            {{-- ---{{ $item }}--- --}}
                            Period Covered :
                        </td>
                        <td>
                            {{ request('start_date', date('Y-m-d')) }}
                        </td>
                        <td>-</td>
                        <td>{{ request('end_date', date('Y-m-d')) }}</td>
                    </tr>
                    <tr class="table-striped ">
                        <td class="font-bold text-center">
                            Shops Included :
                        </td>
                        <td>
                            @if (empty(request('shops', [])))
                                ALL
                            @endif
                            @foreach (request('shops', []) as $shop_id)
                                @php
                                    $shop = $shops->firstWhere('id', $shop_id);
                                @endphp
                                @if ($shop)
                                    @if (!$loop->first)
                                        ,
                                    @endif
                                    {{ $shop->name }}
                                @endif
                            @endforeach
                        </td>

                    </tr>
                </thead>

                <thead>
                    <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Products Sold:
                        </td>
                        <td>
                            {{ number_format($orderItemsData->count(), 0) }}
                        </td>
                    </tr>


                    <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Total Qty Sold:
                        </td>
                        <td>
                            {{ number_format($orderItemsData->sum('soldQuantity'), 0) }}</td>
                    </tr>

                    <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Total Sale:
                        </td>
                        <td>
                            {{ number_format($orderItemsData->sum('soldAmount'), 0) }}</td>
                    </tr>

                </thead>
                <thead>
                    <tr class="thead-primary text-center font-bold italic">
                        <th>Ser</th>
                        {{-- <th>Shop</th> --}}
                        {{-- <th>POS No</th> --}}
                        <th>
                            {{-- <input type="text" class="form-control form-control-sm" id="productFilter"
                                placeholder="Product"> --}}
                            Product Name
                        </th>
                        <th>
                            {{-- <input type="text" class="form-control form-control-sm" id="unitPriceFilter"
                                placeholder="Unit Price"> --}}
                            Unit Price
                        </th>
                        <th>
                            {{-- <input type="text" class="form-control form-control-sm" id="qtyFilter" placeholder="Qty"> --}}
                            Qty
                        </th>
                        <th>
                            {{-- <input type="text" class="form-control form-control-sm" id="amountFilter"
                                placeholder="Amount"> --}}
                            Amount
                        </th>
                        {{-- <th>Customer Name</th> --}}
                        {{-- <th>Order Taken By</th> --}}
                        {{-- <th>Payment Taken By</th> --}}
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // $totalPosNumber = 0;
                    // $totalSoldQty = 0;
                    // $totalSoldAmount = 0;
                    // $totalDiscountAmount = 0;
                    
                    // $totalTotal = 0;
                    ?>
                    @foreach ($orderItemsData as $orderItemsVector)
                        <tr>
                            <td style="width:10%">{{ $loop->index + 1 }}</td>

                            {{-- <td title="{{ $orderItem }}">
                                    {{ $orderItem->order->shop->name }}
                                </td> --}}
                            {{-- <td class="text-center" title="{{ $orderItem->order }}">
                                    <a href="{{ route('orders.show', ['order' => $orderItem->order->id]) }}">
                                        {{ $orderItem->order->POS_number }}
                                    </a>
                                </td> --}}
                            <td style="width:40%" title="{{ print_r($orderItemsVector, true) }}">
                                <a href="{{ route('products.edit', ['product' => $orderItemsVector['product']->id]) }}">
                                    {{ $orderItemsVector['product']->name }}
                                </a>
                            </td>
                            <td class="text-right" style="width:10%">
                                {{ number_format($orderItemsVector['product']->price, 2) }}
                            </td>
                            <td class="text-right" style="width:10%">
                                {{ $orderItemsVector['soldQuantity'] }}
                            </td>
                            <td class="text-right" style="width:10%">
                                {{ number_format($orderItemsVector['soldAmount'], 2) }}
                            </td>
                            {{-- <td class="text-right">
                                    {{ $orderItem->order?->customer?->name }}
                                </td>
                                <td>
                                    {{ $orderItem->order->user->getFullName() }}</td> --}}
                        </tr>
                        {{-- <td>{{ $order->user->getFullName() }}</td>
                                <td>
                                    @foreach ($order->payments->groupBy('user_id') as $userId => $payments)
                                        {{ $payments->first()->user->getFullName() . ' - (' . number_format($payments->sum('amount'), 2) . ')' }}<br />
                                    @endforeach
                                </td> --}}
                        <?php
                        // $totalPosNumber += 1;
                        // $totalSoldQty += $orderItem->quantity;
                        // $grandTotalSoldQty += $orderItem->quantity;
                        // $totalSoldAmount += $orderItem->price;
                        // $grandTotalSoldAmount += $orderItem->price;
                        
                        // $totalBalance += $order->balance();
                        // $totalDiscountAmount += $order->discountAmount();
                        // $totalTotal += $order->total();
                        ?>
                    @endforeach
                    {{-- <tr class="table-bordered font-bold  text-right">
                            <td></td>
                            <td>No of Orders:
                                {{ $totalPosNumber }}</td>
                            <td></td>
                            <td>{{ number_format($totalSoldQty, 0) }}</td>
                            <td>G.Total Sale :
                                {{ config('settings.currency_symbol') . number_format($totalSoldAmount, 0) }}</td> --}}
                    {{-- <td>{{ config('settings.currency_symbol') . number_format($totalDiscountAmount, 2) }}</td> --}}
                    {{-- <td></td>
                    <td></td>
                    <td></td> --}}
                    {{-- <td>{{ config('settings.currency_symbol') . number_format($totalTotal, 2) }}</td> --}}
                    {{-- </tr> --}}
                    <tr></tr>
                </tbody>

                <?php
                // Cash 	Chit 	Discount 	Amount
                // $totalCash += $orderItem->price;
                // $totalChit += $totalBalance;
                // $totalDiscount += $totalDiscountAmount;
                // $totalAmount += $totalTotal;
                ?>
                {{-- @endif --}}
                {{-- @endforeach --}}
                {{-- TODO : add summary of totals in footer row --}}

                <thead style="display:none">
                    {{-- style="display:none"> --}}
                    <tr>
                        <td colspan="9" class="text-right font-serif"> QCL-POS Report - Generated by
                            {{ auth()->user()->getFullName() }} on
                            {{ now()->format('d-M y h:i:s A') }}
                        </td>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script type="text/javascript">
        function exportToExcel(tableId) {
            const tableData = removeLinksAndInputs(
                document.getElementById(tableId).outerHTML
            );
            const downloadName = `dailySaleReport_${getRandomNumbers()}.xls`;
            downloadData(tableData, downloadName, "application/vnd.ms-excel");
        }

        function removeLinksAndInputs(html) {
            return html
                .replace(/<A[^>]*>|<\/A>/g, "")
                .replace(/<input[^>]*>|<\/input>/gi, "");
        }

        function getRandomNumbers() {
            const dateObj = new Date();
            const dateTime = `${dateObj.getHours()}${dateObj.getMinutes()}${dateObj.getSeconds()}`;
            return `${dateTime}${Math.floor(Math.random().toFixed(2) * 100)}`;
        }

        function downloadData(data, fileName, type) {
            const a = document.createElement("a");
            // Create a data URI with the table data and specified MIME type
            a.href = `data:${type}, ${encodeURIComponent(data)}`;
            a.download = fileName;
            a.click();
        }


        const table = document.getElementById('salesTable');
        const tableHeaders = table.querySelectorAll('thead th');

        tableHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const columnIndex = Array.prototype.indexOf.call(header.parentNode.children, header);
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const direction = header.classList.contains('asc') ? -1 : 1;

                rows.sort((a, b) => {
                    const aContent = parseFloat(
                        a.children[columnIndex]?.textContent?.replace(/[^0-9.-]/g, "") || ''
                    );
                    const bContent = parseFloat(
                        b.children[columnIndex]?.textContent?.replace(/[^0-9.-]/g, "") || ''
                    );

                    return direction * (aContent - bContent);
                });

                rows.forEach(row => table.querySelector('tbody').appendChild(row));

                tableHeaders.forEach(header => header.classList.remove('asc', 'desc'));
                header.classList.toggle('asc', direction === 1);
                header.classList.toggle('desc', direction === -1);
            });
        });
    </script>
@endsection
