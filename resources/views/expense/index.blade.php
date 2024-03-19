@extends('layouts.model.index')

@section('title')
    {{ 'Expense Index' }}
@endsection
@section('content-header')
    {{ 'Exp:Index' }}
@endsection
@section('content-actions')
    <a href="{{ route('expenses.create') }}" class="btn btn-primary">Create Expense</a>
@endsection
@section('variables')
    @php($tableHeaders = ['Id', 'Head', 'User', 'Amount', 'Notes', 'Actions'])
@endsection

@section('route-index', route('expenses.index'))

@section('table-rows')
    @php($totalAmount = 0)
    @foreach ($expenses->groupBy('head') as $head => $expensesForHead)
        <tr>
            <td colspan="5">{{ $head }}</td>
        </tr>
        @php($subTotalAmount = 0)
        @foreach ($expensesForHead as $expense)
            <tr>
                <td>{{ $expense->id }}</td>
                <td></td>
                <td>{{ $expense->user->first_name }}</td>
                <td>{{ $expense->amount }}</td>
                <td>{{ $expense->notes }}</td>
                <td>
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                    <a class="btn btn-danger btn-delete" href="{{ route('expenses.destroy', $expense) }}"><i
                            class="fas fa-trash"></i></a>
                </td>
            </tr>
            @php($subTotalAmount += $expense->amount)
        @endforeach
        <tr>
            <td colspan="3" class="text-right font-weight-bold">{{ $head }} Total</td>
            <td colspan="2" class="font-weight-bold">{{ config('settings.currency_symbol') }}
                {{ number_format($subTotalAmount, 2) }}</td>
        </tr>
        @php($totalAmount += $subTotalAmount)
    @endforeach
    <tr>
        <td colspan="3" class="text-right font-weight-bold">Grand Total</td>
        <td colspan="2" class="font-weight-bold">{{ config('settings.currency_symbol') }}
            {{ number_format($totalAmount, 2) }}</td>
    </tr>
@endsection
