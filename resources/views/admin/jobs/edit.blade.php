@extends('layouts.admin')

@section('page_heading', 'Employer Edit')
@section('top_buttion')
<a href="{{ route('admin.jobs.index') }}" class="btn addButton" >
    Job List
</a>
@endsection
@section('content')
    @livewire('job.edit', ['id' => $id])
@endsection


