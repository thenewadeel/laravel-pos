@extends('layouts.admin')

@section('title', __('activity.List'))
@section('content-header', __('activity.List'))
@section('content-actions')
    {{-- @if (auth()->user()->type == 'admin')
        <a href="{{ route('activities.index', ['all' => true]) }}" class="btn btn-info btn-sm">
            <i class="fas fa-filter mr-1"></i>{{ __('common.All') }}
        </a>
    @endif
    <a href="{{ route('activities.index') }}" class="btn btn-info btn-sm">
        <i class="fas fa-filter mr-1"></i>
        {{ __('activity.Unpaid') }}
    </a>
    <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">
        <i class="nav-icon fas fa-shopping-cart"></i>
        {{ __('cart.title_Short') }}
    </a> --}}
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        {{-- {{var_dump($activities)}} --}}
        <div class="card-body m-0 p-0 ">
            {{-- <div class="row"> --}}

            {{-- </div> --}}
            {{-- {{ $activities[0] }} --}}
            <table class="table table-responsive table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        {{-- <th>{{ __('activity.ID') }}</th> --}}
                        <th class="col-1 align-middle">{{ __('activity.Id') }}</th>
                        {{-- <th>{{ __('activity.Date') }}</th> --}}
                        <th class="col-2 text-center align-middle">{{ __('activity.Log_Name') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('activity.Description') }}</th>
                        {{-- <th class="col-1 text-center align-middle">{{ __('activity.Subject_Type') }}</th> --}}
                        {{-- <th class="col-1 text-center align-middle">{{ __('activity.Event') }}</th> --}}
                        <th class="text-center align-middle">{{ __('activity.Subject_Id') }}</th>
                        <th class="text-center align-middle">{{ __('activity.Causer') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('activity.Changes') }}</th>

                        <th class="text-center align-middle">{{ __('activity.Date_Time') }}</th>

                        {{-- <th>{{ 'Shop' }}</th> --}}
                    </tr>


                </thead>
                <tbody>
                    @foreach ($activities as $activity)
                        <tr
                            style="{{ $activity->description == 'edited' ? 'color:maroon;' : ($activity->description == 'deleted' ? 'color:red;' : ($activity->description == 'created' ? 'color:green;' : '')) }}">
                            {{-- <td>{{ $activity->id }}</td> --}}
                            <td title="{{ $activity }}" class="px-1 m-0 align-middle">
                                {{-- <a href=""> --}}
                                {{ $activity->id }}
                                {{-- </a> --}}
                            </td>
                            <td class=" align-middle">
                                {{ $activity->log_name }}
                            </td>
                            <td class="text-center align-middle">
                                {{ $activity->description }}
                            </td>
                            {{-- <td class="text-center align-middle">
                                {{ $activity->subject_type }}
                            </td> --}}
                            {{-- <td class=" align-middle">
                                {{ $activity->event }}
                            </td> --}}
                            <td class="text-right align-middle">
                                {{ $activity->subject?->id }}
                            </td>
                            <td class="text-right align-middle">
                                {{ $activity->causer?->getFullName() }}
                            </td>
                            <td class="text-right align-middle">
                                @if ($activity->description == 'updated')
                                    <table>
                                        {{-- <thead>
                                            <tr>
                                                <td colspan="3" class="text-center font-bold">
                                                    {{ __('activity.Changes') }}</td>
                                            </tr>
                                        </thead> --}}
                                        @foreach ($activity->properties['attributes'] as $key => $value)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                <td>{{ $activity->properties['old'][$key] }}</td>
                                                <td>{{ $value }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @elseif ($activity->description == 'edited')
                                    <table>
                                        <thead>
                                            <tr>
                                                <td colspan="3">{{ __('activity.Changes') }}</td>
                                            </tr>
                                        </thead>
                                        @foreach ($activity->properties as $key => $value)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                {{-- <td>{{ $activity->properties['old'][$key] }}</td> --}}
                                                @foreach ($value as $k => $val)
                                                    <td>{{ $val['name'] }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </table>
                                    {{-- {{ $activity->properties }} --}}
                                @else
                                    @switch($activity->description)
                                        @case('created')
                                            @if ($activity->log_name == 'orders')
                                                {{ $activity->properties['attributes']['POS_number'] }}
                                            @elseif ($activity->log_name == 'orders-items')
                                                @php($order = App\Models\Order::find($activity->properties['attributes']['order_id']))
                                                <a href="{{ route('orders.show', $order) }}" title="{{ $activity->properties }}">
                                                    {{ $order->POS_number }}
                                                </a>
                                                <br />
                                                <div>
                                                    Items :
                                                    {{ App\Models\Product::find($activity->properties['attributes']['product_id'])->name }}
                                                </div>
                                                <div>
                                                    Qty: {{ $activity->properties['attributes']['quantity'] }}
                                                </div>
                                            @endif
                                        @break

                                        @case('deleted')
                                            @if ($activity->log_name == 'orders')
                                                {{ $activity->properties['attributes']['POS_number'] ??'err' }}
                                            @elseif ($activity->log_name == 'orders-items')
                                                @php($order = App\Models\Order::find($activity->properties['old']['order_id']))
                                                <a href="{{ route('orders.show', $order) }}" title="{{ $activity->properties }}">
                                                    {{ $order->POS_number }}
                                                </a>
                                                <br />
                                                <div>
                                                    Items :
                                                    {{ App\Models\Product::find($activity->properties['old']['product_id'])->name }}
                                                </div>
                                                <div>
                                                    Qty: {{ $activity->properties['old']['quantity'] }}
                                                </div>
                                            @endif
                                            {{-- D{{ $activity->properties }}X --}}
                                        @break
                                    @endswitch
                                @endif
                            </td>
                            <td class=" align-middle">
                                {{ $activity->updated_at }}
                            </td>


                            <td class="p-0 m-0 text-right  align-middle">
                                <div class="btn-group">

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- {{ $activities->render() }} --}}
        </div>
    </div>
@endsection
