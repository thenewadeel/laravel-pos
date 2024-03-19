@extends('layouts.model.create')

@section('title')
    {{ 'Expense Create' }}
@endsection
@section('content-header')
    {{ 'Exp:Create' }}
@endsection
@section('content-actions')
@endsection

@section('variables')
    @php($varName = 'test-variable')
    @php($varValue = 'test-value')
    @php($varData = ['test' => 'data'])
@endsection

@section('route-store', route('expenses.store'))

@section('form-fields')

    <div class="form-group">
        <label for="head">{{ __('expense.Head') }}</label>
        <input type="text" name="head" class="form-control @error('head') is-invalid @enderror" id="head"
            placeholder="{{ __('expense.Head') }}">
        @error('head')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        {{-- <label for="user_id">{{ __('expense.User') }}</label> --}}
        @component('layouts.partials.selector', [
            'name' => 'user_id',
            'label' => __('expense.User'),
            'options' => \App\Models\User::pluck('first_name', 'id')->toArray(),
        ])
        @endcomponent
        @error('user_id')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="amount">{{ __('expense.Amount') }}</label>
        <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount"
            placeholder="{{ __('expense.Amount') }}">
        @error('amount')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="notes">{{ __('expense.Notes') }}</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" id="notes"
            placeholder="{{ __('expense.Notes') }}"></textarea>
        @error('notes')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

@endsection
