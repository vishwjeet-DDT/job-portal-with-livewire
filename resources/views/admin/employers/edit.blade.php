@extends('layouts.admin')

@section('page_heading', 'Employer Edit')
@section('top_buttion')
<a href="{{ route('admin.employers.index') }}" class="btn addButton" >
    Employer List
</a>
@endsection
@section('content')
    @livewire('employer.edit', ['id' => $id])
@endsection


