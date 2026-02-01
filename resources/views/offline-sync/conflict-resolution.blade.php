@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Conflict Resolution</h1>
            <p class="text-muted">Resolve synchronization conflicts between tablet and server</p>
            
            <livewire:conflict-resolution-component />
        </div>
    </div>
</div>
@endsection
