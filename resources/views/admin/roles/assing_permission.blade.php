@extends('layouts.admin')

@section('page_title', 'Assign Role & Permission')

@section('page_heading')
Assign Role & Permission
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        @livewire('role.role-permission')
    </div>
</div>

@endsection
