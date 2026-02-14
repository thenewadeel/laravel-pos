@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Tablet Order Entry</h1>
            <p class="text-muted">Create orders for offline tablet devices</p>
            
            <livewire:tablet-order-component />
        </div>
    </div>
</div>
@endsection
