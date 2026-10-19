@extends('layouts.admin')

@section('page_heading','Apply Job')
@section('content')
@livewire('job.apply-list',['job'=>$job])
@endsection
