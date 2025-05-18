@extends('layouts.admin')

@section('title', __('common.Cashiers_Report'))
@section('content-header', __('common.Cashiers_Report'))

@section('content')
    @include('layouts.partials.collapsibleFilterForm', [
        'errors' => $errors,
        'target_route_name' => 'reports.cashiersReport',
    ])
    <div class="card">
        <div class="card-header font-bold text-lg">
            <div class=" flex flex-row justify-between">
                {{ config('settings.club_initials') }}-POS Cashiers Sale Report

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
                <thead>
                    <tr class="table-primary">
                        <td colspan="5" class="text-center font-serif font-bold text-lg">
                            {{ config('settings.club_initials') }}-POS Cashiers Sale Report
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
                    {{-- <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Products Sold:
                        </td>
                        <td>
                            {{ number_format($cashiersData->count(), 0) }}
                        </td>
                    </tr> --}}


                    {{-- <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Total Qty Sold:
                        </td>
                        <td>
                            {{ number_format($cashiersData->sum('soldQuantity'), 0) }}</td>
                    </tr> --}}

                    {{-- <tr class="table-striped font-bold  text-right">
                        <td colspan="4">
                            Total Sale:
                        </td>
                        <td>
                            {{ number_format($cashiersData->sum('soldAmount'), 0) }}</td>
                    </tr> --}}

                </thead>
                <thead>
                    <tr class="thead-primary text-center font-bold italic">
                        <th>Ser</th>
                        {{-- <th>Shop</th> --}}
                        {{-- <th>POS No</th> --}}
                        <th>
                            User Name
                        </th>
                        <th>
                            No of Payments Received
                        </th>
                        <th>
                            Total Amount
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashiersData as $cashiersVector)
                        <tr>
                            <td style="width:10%">{{ $loop->index + 1 }}</td>

                            <td style="width:40%" title="{{ print_r($cashiersVector, true) }}">
                                <a href="{{ route('users.edit', ['user' => $cashiersVector['user']->id]) }}">
                                    {{ $cashiersVector['user']->getFullname() }}
                                </a>
                            </td>
                            <td class="text-right" style="width:10%"
                                onclick="toggleDetails('details-{{ $cashiersVector['user']->id }}')">
                                {{ number_format($cashiersVector['paymentsCount'], 0) }}
                                <i class="fa fa-chevron-circle-down" style="cursor:pointer"></i>
                            </td>
                            <td class="text-right" style="width:10%">
                                {{ number_format($cashiersVector['paymentsTotal'], 2) }}
                            </td>
                            {{-- <td class="text-right">
                                    {{ $orderItem->order?->customer?->name }}
                                </td>
                                <td>
                                    {{ $orderItem->order->user->getFullName() }}</td> --}}
                        </tr>
                        <tr id="details-{{ $cashiersVector['user']->id }}" class="hidden">
                            <td colspan="1"></td>
                            <td colspan="3">
                                <table class="w-[100%]">
                                    <tr>
                                        <th>POS #</th>
                                        <th>Shop</th>
                                        <th class="text-right">Balance</th>
                                        <th class="text-right">Received</th>
                                    </tr>
                                    @foreach ($cashiersVector['orders'] as $order)
                                        @include('layouts.partials.orderPeek', [
                                            'order' => $order,
                                            'asTable' => true,
                                        ])
                                    @endforeach
                            </td>
            </table>
            </tr>
            @endforeach

            </tbody>


            <thead style="display:none">
                {{-- style="display:none"> --}}
                <tr>
                    <td colspan="9" class="text-right font-serif"> {{ config('settings.club_initials') }}-POS Report -
                        Generated by
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
            const downloadName = `CashiersReport_${getRandomNumbers()}.xls`;
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

        function toggleDetails(id) {
            document.getElementById(id).classList.toggle('hidden');
        }
    </script>
@endsection
