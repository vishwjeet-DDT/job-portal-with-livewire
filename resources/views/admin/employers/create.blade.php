@extends('layouts.admin')

@section('page_heading', 'Employer Create')
@section('top_buttion')
<a href="{{ route('admin.employers.index') }}" class="btn addButton" >
    Employer List
</a>
@endsection
@section('content')
@livewire('employer.create')
@endsection


