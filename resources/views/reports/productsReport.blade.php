@extends('layouts.admin')

@section('title', __('common.Products_Report'))
@section('content-header', __('common.Products_Report'))

@section('content')
    @include('layouts.partials.collapsibleFilterForm', [
        'errors' => $errors,
        'target_route_name' => 'reports.productsReport',
    ])
    <div class="card">
        <div class="card-header font-bold text-lg">
            <div class=" flex flex-row justify-between">
                {{ config('settings.club_initials') }}-POS Products Sale Report

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
                            {{ config('settings.club_initials') }}-POS Products Sale Report
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
                                @if ($orderItemsVector['product_name'])
                                    {{ $orderItemsVector['product_name'] }}
                            </td>
                            <td class="text-right" style="width:10%">
                                {{ number_format($orderItemsVector['product_price'], 2) }}
                            @else
                                <span class="text-danger"> No Product Valid </span>
                    @endif
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
                        <td colspan="9" class="text-right font-serif">
                            {{ config('settings.club_initials') }}-POS Report - Generated by
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
            const downloadName = `ProductsSaleReport_${getRandomNumbers()}.xls`;
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
