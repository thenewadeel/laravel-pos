@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1>Sync Status Dashboard</h1>
            <p class="text-muted">Monitor and manage offline order synchronization</p>
            
            <livewire:sync-status-component />
        </div>
    </div>
</div>
@endsection
