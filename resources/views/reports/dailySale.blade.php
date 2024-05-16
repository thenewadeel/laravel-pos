@extends('layouts.admin')

@section('title', __('common.Daily_Sale'))
@section('content-header', __('common.Daily_Sale'))

@section('content')
    <div class="card">
        <div class="card-header font-bold text-lg">
            Filters
        </div>
        <div class="card-body">
            {{-- {{ $orders }}
            <hr />
            {{ $openOrders }} --}}
            <form class="mb-4" action="{{ route('reports.dailySale') }}" method="GET">
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
                                        value="{{ $shop->id }}" id="shop{{ $shop->id }}">
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
            Report
        </div>
        <div class="card-body">
            <table id="salesTable" class="table table-bordered table-striped table-hover table-sm">
                <thead style="display:none">
                    {{-- style="display:none"> --}}
                    <tr>
                        <td colspan="9" class="text-right font-serif"> QCL-POS Daily Shop Wise Sales Report -
                            {{ request('date', date('Y-m-d')) }}
                        </td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                    <tr></tr>
                </thead>
                <?php
                //total 	Chit 	Discount 	Amount
                $totalCash = 0;
                $totalChit = 0;
                $totalDiscount = 0;
                $totalAmount = 0;
                ?>
                @foreach ($shops->whereIn('id', collect($orders)->pluck('shop_id')->unique()->toArray()) as $shop)
                    @if ($shopOrders = collect($orders)->where('shop_id', $shop->id)->sortBy('date')->sortBy('time'))
                        <thead>
                            <tr class="table-primary">
                                <td colspan="9" class="">
                                    <span class="font-extrabold">
                                        {{ $shop?->name }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="thead-primary text-center font-bold italic">
                                {{-- <th>Ser</th> --}}
                                <th>POS No</th>
                                <th>Cash</th>
                                <th>Chit</th>
                                <th>Discount</th>
                                {{-- <th>Charges</th>
        <th>POS</th>
        <th>Online</th> --}}
                                <th>Amount</th>
                                <th>Customer Name</th>
                                <th>Order Taken By</th>
                                <th>Payment Taken By</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $totalPosNumber = 0;
                            $totalReceivedAmount = 0;
                            $totalBalance = 0;
                            $totalDiscountAmount = 0;
                            $totalTotal = 0;
                            ?>
                            @foreach ($shopOrders as $order)
                                <tr>
                                    <?php
                                    $totalPosNumber += 1;
                                    $totalReceivedAmount += $order->receivedAmount();
                                    $totalBalance += $order->balance();
                                    $totalDiscountAmount += $order->discountAmount();
                                    $totalTotal += $order->total();
                                    ?>
                                    <td title="{{ $order->payments }}"><a
                                            href="{{ route('orders.show', ['order' => $order->id]) }}">{{ $order->POS_number }}</a>
                                    </td>
                                    <td class="text-right">{{ number_format($order->receivedAmount(), 2) }}</td>
                                    <td class="text-right">{{ number_format($order->balance(), 2) }}</td>
                                    <td class="text-right">{{ number_format($order->discountAmount(), 2) }}</td>
                                    {{-- <td>{{ $order->chr }}</td>
                    <td>{{ $order->pos }}</td>
                    <td>{{ $order->online }}</td> --}}
                                    <td class="text-right">{{ number_format($order->total(), 2) }}</td>
                                    <td>{{ $order->customer ? $order->customer->name : 'unknown' }}</td>
                                    <td>{{ $order->user->getFullName() }}</td>
                                    <td>
                                        @foreach ($order->payments->groupBy('user_id') as $userId => $payments)
                                            {{ $payments->first()->user->getFullName() . ' - (' . number_format($payments->sum('amount'), 2) . ')' }}<br />
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="table-bordered font-bold  text-right">
                                <td>Total ({{ $totalPosNumber }})</td>
                                <td>{{ config('settings.currency_symbol') . number_format($totalReceivedAmount, 2) }}</td>
                                <td>{{ config('settings.currency_symbol') . number_format($totalBalance, 2) }}</td>
                                <td>{{ config('settings.currency_symbol') . number_format($totalDiscountAmount, 2) }}</td>
                                {{-- <td></td>
                    <td></td>
                    <td></td> --}}
                                <td>{{ config('settings.currency_symbol') . number_format($totalTotal, 2) }}</td>
                            </tr>
                            <tr></tr>
                        </tbody>

                        <?php
                        // Cash 	Chit 	Discount 	Amount
                        $totalCash += $totalReceivedAmount;
                        $totalChit += $totalBalance;
                        $totalDiscount += $totalDiscountAmount;
                        $totalAmount += $totalTotal;
                        ?>
                    @endif
                @endforeach
                {{-- TODO : add summary of totals in footer row --}}
                <thead>
                    <tr class="table-danger font-extrabold  text-right">
                        <td colspan="7">
                            G. Total Cash:</td>
                        <td> {{ config('settings.currency_symbol') . number_format($totalCash, 2) }}
                        </td>
                    </tr>
                    <tr class="table-danger font-extrabold  text-right">
                        <td colspan="7">
                            Chit: </td>
                        <td> {{ config('settings.currency_symbol') . number_format($totalChit, 2) }}
                        </td>
                    </tr>
                    <tr class="table-danger font-extrabold  text-right">
                        <td colspan="7">
                            Discount:</td>
                        <td> {{ config('settings.currency_symbol') . number_format($totalDiscount, 2) }}
                        </td>
                    </tr>
                    <tr class="table-danger font-extrabold  text-right">
                        <td colspan="7">
                            G. Total Amount:</td>
                        <td> {{ config('settings.currency_symbol') . number_format($totalAmount, 2) }}
                        </td>
                    </tr>

                    <tr class="table-info font-extrabold  text-right">
                        <td colspan="7">
                            Total Closed Orders: </td>
                        <td>{{ $orders->count() }}</td>
                    </tr>
                    <tr class="table-info font-extrabold  text-right">
                        <td colspan="7">
                            Total Open Orders:</td>
                        <td> {{ $openOrders->count() }}</td>
                    </tr>
                    <tr class="table-info font-extrabold  text-right">
                        {{-- Total Cashiers: {{ count($cashiers) }}<br /> --}}
                        <td colspan="7">
                            Total Open Amount:</td>
                        <td>
                            {{ config('settings.currency_symbol') .
                                number_format(
                                    $openOrders->map(function ($i) {
                                            return $i->total();
                                        })->sum(),
                                    2,
                                ) }}
                        </td>
                        {{-- </td> --}}
                    </tr>
                </thead>
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
            <div class="card-footer">
                <button onclick="exportToExcel('salesTable', 'Sales Report {{ request('date', date('Y-m-d')) }}')"
                    class="btn btn-info">{{ __('common.Download_To_Excel') }}</button>
            </div>
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
    </script>
@endsection
