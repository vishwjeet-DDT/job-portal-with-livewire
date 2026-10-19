@extends('layouts.admin')

@section('page_heading','Releted Candidates')
@section('content')
@livewire('job.releted-candidate',['job'=>$job])
@endsection
